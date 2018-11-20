<?php

use Engelsystem\ShiftSignupState;

/**
 * @param array $shift
 * @return string
 */
function shift_link($shift)
{
    $parameters = ['action' => 'view'];
    if (isset($shift['SID'])) {
        $parameters['shift_id'] = $shift['SID'];
    }

    $link = page_link_to('shifts', $parameters);

    return $link;
}

/**
 * @param array $shift
 * @return string
 */
function shift_delete_link($shift)
{
    return page_link_to('user_shifts', ['delete_shift' => $shift['SID']]);
}

/**
 * @param array $shift
 * @return string
 */
function shift_edit_link($shift)
{
    return page_link_to('user_shifts', ['edit_shift' => $shift['SID']]);
}

/**
 * Edit a single shift.
 *
 * @return string
 */
function shift_edit_controller()
{
    global $privileges;

    $msg = '';
    $valid = true;
    $request = request();

    if (!in_array('admin_shifts', $privileges)) {
        redirect(page_link_to('user_shifts'));
    }

    if (!$request->has('edit_shift') || !test_request_int('edit_shift')) {
        redirect(page_link_to('user_shifts'));
    }
    $shift_id = $request->input('edit_shift');

    $shift = Shift($shift_id);

    $room = select_array(Rooms(), 'RID', 'Name');
    $angeltypes = select_array(AngelTypes(), 'id', 'name');
    $shifttypes = select_array(ShiftTypes(), 'id', 'name');

    $needed_angel_types = select_array(
        NeededAngelTypes_by_shift($shift_id),
        'angel_type_id',
        'count'
    );
    foreach (array_keys($angeltypes) as $angeltype_id) {
        if (!isset($needed_angel_types[$angeltype_id])) {
            $needed_angel_types[$angeltype_id] = 0;
        }
    }

    $shifttype_id = $shift['shifttype_id'];
    $title = $shift['title'];
    $rid = $shift['RID'];
    $start = $shift['start'];
    $end = $shift['end'];

    if ($request->hasPostData('submit')) {
        // Name/Bezeichnung der Schicht, darf leer sein
        $title = strip_request_item('title');

        // Auswahl der sichtbaren Locations für die Schichten
        if (
            $request->has('rid')
            && preg_match('/^\d+$/', $request->input('rid'))
            && isset($room[$request->input('rid')])
        ) {
            $rid = $request->input('rid');
        } else {
            $valid = false;
            $msg .= error(__('Please select a room.'), true);
        }

        if ($request->has('shifttype_id') && isset($shifttypes[$request->input('shifttype_id')])) {
            $shifttype_id = $request->input('shifttype_id');
        } else {
            $valid = false;
            $msg .= error(__('Please select a shifttype.'), true);
        }

        if ($request->has('start') && $tmp = parse_date('Y-m-d H:i', $request->input('start'))) {
            $start = $tmp;
        } else {
            $valid = false;
            $msg .= error(__('Please enter a valid starting time for the shifts.'), true);
        }

        if ($request->has('end') && $tmp = parse_date('Y-m-d H:i', $request->input('end'))) {
            $end = $tmp;
        } else {
            $valid = false;
            $msg .= error(__('Please enter a valid ending time for the shifts.'), true);
        }

        if ($start >= $end) {
            $valid = false;
            $msg .= error(__('The ending time has to be after the starting time.'), true);
        }

        foreach ($needed_angel_types as $needed_angeltype_id => $count) {
            $needed_angel_types[$needed_angeltype_id] = 0;

            $queryKey = 'type_' . $needed_angeltype_id;
            if ($request->has($queryKey)) {
                if (test_request_int($queryKey)) {
                    $needed_angel_types[$needed_angeltype_id] = trim($request->input($queryKey));
                } else {
                    $valid = false;
                    $msg .= error(sprintf(
                        __('Please check your input for needed angels of type %s.'),
                        $angeltypes[$needed_angeltype_id]
                    ), true);
                }
            }
        }

        if ($valid) {
            $shift['shifttype_id'] = $shifttype_id;
            $shift['title'] = $title;
            $shift['RID'] = $rid;
            $shift['start'] = $start;
            $shift['end'] = $end;

            Shift_update($shift);
            NeededAngelTypes_delete_by_shift($shift_id);
            $needed_angel_types_info = [];
            foreach ($needed_angel_types as $type_id => $count) {
                NeededAngelType_add($shift_id, $type_id, null, $count);
                if ($count > 0) {
                    $needed_angel_types_info[] = $angeltypes[$type_id] . ': ' . $count;
                }
            }

            engelsystem_log(
                'Updated shift \'' . $shifttypes[$shifttype_id] . ', ' . $title
                . '\' from ' . date('Y-m-d H:i', $start)
                . ' to ' . date('Y-m-d H:i', $end)
                . ' with angel types ' . join(', ', $needed_angel_types_info)
            );
            success(__('Shift updated.'));

            redirect(shift_link([
                'SID' => $shift_id
            ]));
        }
    }

    $angel_types_spinner = '';
    foreach ($angeltypes as $angeltype_id => $angeltype_name) {
        $angel_types_spinner .= form_spinner('type_' . $angeltype_id, $angeltype_name,
            $needed_angel_types[$angeltype_id]);
    }

    return page_with_title(
        shifts_title(),
        [
            msg(),
            '<noscript>'
            . info(__('This page is much more comfortable with javascript.'), true)
            . '</noscript>',
            form([
                form_select('shifttype_id', __('Shifttype'), $shifttypes, $shifttype_id),
                form_text('title', __('Title'), $title),
                form_select('rid', __('Room:'), $room, $rid),
                form_text('start', __('Start:'), date('Y-m-d H:i', $start)),
                form_text('end', __('End:'), date('Y-m-d H:i', $end)),
                '<h2>' . __('Needed angels') . '</h2>',
                $angel_types_spinner,
                form_submit('submit', __('Save'))
            ])
        ]
    );
}

/**
 * @return string
 */
function shift_delete_controller()
{
    global $privileges;
    $request = request();

    if (!in_array('user_shifts_admin', $privileges)) {
        redirect(page_link_to('user_shifts'));
    }

    // Schicht komplett löschen (nur für admins/user mit user_shifts_admin privileg)
    if (!$request->has('delete_shift') || !preg_match('/^\d+$/', $request->input('delete_shift'))) {
        redirect(page_link_to('user_shifts'));
    }
    $shift_id = $request->input('delete_shift');

    $shift = Shift($shift_id);
    if (empty($shift)) {
        redirect(page_link_to('user_shifts'));
    }

    // Schicht löschen bestätigt
    if ($request->hasPostData('delete')) {
        Shift_delete($shift_id);

        engelsystem_log(
            'Deleted shift ' . $shift['name']
            . ' from ' . date('Y-m-d H:i', $shift['start'])
            . ' to ' . date('Y-m-d H:i', $shift['end'])
        );
        success(__('Shift deleted.'));
        redirect(page_link_to('user_shifts'));
    }

    return page_with_title(shifts_title(), [
        error(sprintf(
            __('Do you want to delete the shift %s from %s to %s?'),
            $shift['name'],
            date('Y-m-d H:i', $shift['start']),
            date('H:i', $shift['end'])
        ), true),
        form([
            form_hidden('delete_shift', $shift_id),
            form_submit('delete', __('delete')),
        ]),
    ]);
}

/**
 * @return array
 */
function shift_controller()
{
    global $privileges;
    $user = auth()->user();
    $request = request();

    if (!in_array('user_shifts', $privileges)) {
        redirect(page_link_to('/'));
    }

    if (!$request->has('shift_id')) {
        redirect(page_link_to('user_shifts'));
    }

    $shift = Shift($request->input('shift_id'));
    if (empty($shift)) {
        error(__('Shift could not be found.'));
        redirect(page_link_to('user_shifts'));
    }

    $shifttype = ShiftType($shift['shifttype_id']);
    $room = Room($shift['RID']);
    $angeltypes = AngelTypes();
    $user_shifts = Shifts_by_user($user->id);

    $shift_signup_state = new ShiftSignupState(ShiftSignupState::OCCUPIED, 0);
    foreach ($angeltypes as &$angeltype) {
        $needed_angeltype = NeededAngeltype_by_Shift_and_Angeltype($shift, $angeltype);
        if (empty($needed_angeltype)) {
            continue;
        }

        $shift_entries = ShiftEntries_by_shift_and_angeltype($shift['SID'], $angeltype['id']);

        $angeltype_signup_state = Shift_signup_allowed(
            $user,
            $shift,
            $angeltype,
            null,
            $user_shifts,
            $needed_angeltype,
            $shift_entries
        );
        $shift_signup_state->combineWith($angeltype_signup_state);
        $angeltype['shift_signup_state'] = $angeltype_signup_state;
    }

    return [
        $shift['name'],
        Shift_view($shift, $shifttype, $room, $angeltypes, $shift_signup_state)
    ];
}

/**
 * @return array|false
 */
function shifts_controller()
{
    $request = request();
    if (!$request->has('action')) {
        redirect(page_link_to('user_shifts'));
    }

    switch ($request->input('action')) {
        case 'view':
            return shift_controller();
        /** @noinspection PhpMissingBreakStatementInspection */
        case 'next':
            shift_next_controller();
        default:
            redirect(page_link_to('/'));
    }

    return false;
}

/**
 * Redirects the user to his next shift.
 */
function shift_next_controller()
{
    global $privileges;

    if (!in_array('user_shifts', $privileges)) {
        redirect(page_link_to('/'));
    }

    $upcoming_shifts = ShiftEntries_upcoming_for_user(auth()->user()->id);

    if (!empty($upcoming_shifts)) {
        redirect(shift_link($upcoming_shifts[0]));
    }

    redirect(page_link_to('user_shifts'));
}

/**
 * Export filtered shifts via JSON.
 * (Like iCal Export or shifts view)
 */
function shifts_json_export_controller()
{
    $request = request();

    if (!$request->has('key') || !preg_match('/^[\da-f]{32}$/', $request->input('key'))) {
        engelsystem_error('Missing key.');
    }

    $user = auth()->apiUser('key');
    if (!$user) {
        engelsystem_error('Key invalid.');
    }
    if (!in_array('shifts_json_export', privileges_for_user($user->id))) {
        engelsystem_error('No privilege for shifts_json_export.');
    }

    $shifts = load_ical_shifts();

    header('Content-Type: application/json; charset=utf-8');
    raw_output(json_encode($shifts));
}

/**
 * Returns users shifts to export.
 *
 * @return array
 */
function load_ical_shifts()
{
    return Shifts_by_user(auth()->user()->id);
}
