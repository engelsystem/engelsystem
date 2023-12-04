<?php

use Engelsystem\Models\AngelType;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Room;
use Engelsystem\Models\Shifts\ScheduleShift;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Models\Shifts\ShiftSignupStatus;
use Engelsystem\ShiftSignupState;

/**
 * @param array|Shift $shift
 * @return string
 */
function shift_link($shift)
{
    $parameters = ['action' => 'view'];
    if (isset($shift['shift_id']) || isset($shift['id'])) {
        $parameters['shift_id'] = $shift['shift_id'] ?? $shift['id'];
    }

    return page_link_to('shifts', $parameters);
}

/**
 * @param Shift $shift
 * @return string
 */
function shift_delete_link(Shift $shift)
{
    return page_link_to('user_shifts', ['delete_shift' => $shift->id]);
}

/**
 * @param Shift $shift
 * @return string
 */
function shift_edit_link(Shift $shift)
{
    return page_link_to('user_shifts', ['edit_shift' => $shift->id]);
}

/**
 * Edit a single shift.
 *
 * @return string
 */
function shift_edit_controller()
{
    $valid = true;
    $request = request();

    if (!auth()->can('admin_shifts')) {
        throw_redirect(page_link_to('user_shifts'));
    }

    if (!$request->has('edit_shift') || !test_request_int('edit_shift')) {
        throw_redirect(page_link_to('user_shifts'));
    }
    $shift_id = $request->input('edit_shift');

    $shift = Shift::findOrFail($shift_id);
    if (ScheduleShift::whereShiftId($shift->id)->first()) {
        warning(__(
            'This shift was imported from a schedule so some changes will be overwritten with the next import.'
        ));
    }

    $rooms = [];
    foreach (Room::orderBy('name')->get() as $room) {
        $rooms[$room->id] = $room->name;
    }
    $angeltypes = AngelType::all()->pluck('name', 'id')->toArray();
    $shifttypes = ShiftType::all()->pluck('name', 'id')->toArray();

    $needed_angel_types = collect(NeededAngelTypes_by_shift($shift_id))->pluck('count', 'angel_type_id')->toArray();
    foreach (array_keys($angeltypes) as $angeltype_id) {
        if (!isset($needed_angel_types[$angeltype_id])) {
            $needed_angel_types[$angeltype_id] = 0;
        }
    }

    $shifttype_id = $shift->shift_type_id;
    $title = $shift->title;
    $description = $shift->description;
    $rid = $shift->room_id;
    $start = $shift->start;
    $end = $shift->end;

    if ($request->hasPostData('submit')) {
        // Name/Bezeichnung der Schicht, darf leer sein
        $title = strip_request_item('title');
        $description = strip_request_item_nl('description');

        // Auswahl der sichtbaren Locations für die Schichten
        if (
            $request->has('rid')
            && preg_match('/^\d+$/', $request->input('rid'))
            && isset($rooms[$request->input('rid')])
        ) {
            $rid = $request->input('rid');
        } else {
            $valid = false;
            error(__('Please select a room.'));
        }

        if ($request->has('shifttype_id') && isset($shifttypes[$request->input('shifttype_id')])) {
            $shifttype_id = $request->input('shifttype_id');
        } else {
            $valid = false;
            error(__('Please select a shifttype.'));
        }

        if ($request->has('start') && $tmp = DateTime::createFromFormat('Y-m-d H:i', $request->input('start'))) {
            $start = $tmp;
        } else {
            $valid = false;
            error(__('Please enter a valid starting time for the shifts.'));
        }

        if ($request->has('end') && $tmp = DateTime::createFromFormat('Y-m-d H:i', $request->input('end'))) {
            $end = $tmp;
        } else {
            $valid = false;
            error(__('Please enter a valid ending time for the shifts.'));
        }

        if ($start >= $end) {
            $valid = false;
            error(__('The ending time has to be after the starting time.'));
        }

        foreach ($needed_angel_types as $needed_angeltype_id => $count) {
            $needed_angel_types[$needed_angeltype_id] = 0;

            $queryKey = 'angeltype_count_' . $needed_angeltype_id;
            if ($request->has($queryKey)) {
                if (test_request_int($queryKey)) {
                    $needed_angel_types[$needed_angeltype_id] = trim($request->input($queryKey));
                } else {
                    $valid = false;
                    error(sprintf(
                        __('Please check your input for needed angels of type %s.'),
                        $angeltypes[$needed_angeltype_id]
                    ));
                }
            }
        }

        if ($valid) {
            $oldShift = Shift::find($shift->id);

            $shift->shift_type_id = $shifttype_id;
            $shift->title = $title;
            $shift->description = $description;
            $shift->room_id = $rid;
            $shift->start = $start;
            $shift->end = $end;
            $shift->updatedBy()->associate(auth()->user());
            $shift->save();

            mail_shift_change($oldShift, $shift);

            NeededAngelType::whereShiftId($shift_id)->delete();
            $needed_angel_types_info = [];
            foreach ($needed_angel_types as $type_id => $count) {
                $angeltype = AngelType::find($type_id);
                if (!empty($angeltype) && $count > 0) {
                    $neededAngelType = new NeededAngelType();
                    $neededAngelType->shift()->associate($shift);
                    $neededAngelType->angel_type_id = $type_id;
                    $neededAngelType->count = $count;
                    $neededAngelType->save();

                    $needed_angel_types_info[] = $angeltypes[$type_id] . ': ' . $count;
                }
            }

            engelsystem_log(
                'Updated shift \'' . $shifttypes[$shifttype_id] . ', ' . $title
                . '\' from ' . $start->format('Y-m-d H:i')
                . ' to ' . $end->format('Y-m-d H:i')
                . ' with angel types ' . join(', ', $needed_angel_types_info)
                . ' and description ' . $description
            );
            success(__('Shift updated.'));

            throw_redirect(shift_link($shift));
        }
    }

    $angel_types_spinner = '';
    foreach ($angeltypes as $angeltype_id => $angeltype_name) {
        $angel_types_spinner .= form_spinner(
            'angeltype_count_' . $angeltype_id,
            htmlspecialchars($angeltype_name),
            $needed_angel_types[$angeltype_id]
        );
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
                form_select('rid', __('Room:'), $rooms, $rid),
                form_text('start', __('Start:'), $start->format('Y-m-d H:i')),
                form_text('end', __('End:'), $end->format('Y-m-d H:i')),
                form_textarea('description', __('Additional description'), $description),
                form_info(
                    '',
                    __('This description is for single shifts, otherwise please use the description in shift type.')
                ),
                '<h2>' . __('Needed angels') . '</h2>',
                $angel_types_spinner,
                form_submit('submit', __('Save')),
            ]),
        ]
    );
}

/**
 * @return string
 */
function shift_delete_controller()
{
    $request = request();

    if (!auth()->can('user_shifts_admin')) {
        throw_redirect(page_link_to('user_shifts'));
    }

    // Schicht komplett löschen (nur für admins/user mit user_shifts_admin privileg)
    if (!$request->has('delete_shift') || !preg_match('/^\d+$/', $request->input('delete_shift'))) {
        throw_redirect(page_link_to('user_shifts'));
    }
    $shift_id = $request->input('delete_shift');

    $shift = Shift($shift_id);
    if (empty($shift)) {
        throw_redirect(page_link_to('user_shifts'));
    }

    // Schicht löschen bestätigt
    if ($request->hasPostData('delete')) {
        foreach ($shift->shiftEntries as $entry) {
            event('shift.entry.deleting', [
                'user'       => $entry->user,
                'start'      => $shift->start,
                'end'        => $shift->end,
                'name'       => $shift->shiftType->name,
                'title'      => $shift->title,
                'type'       => $entry->angelType->name,
                'room'       => $shift->room,
                'freeloaded' => $entry->freeloaded,
            ]);
        }

        $shift->delete();

        engelsystem_log(
            'Deleted shift ' . $shift->title . ': ' . $shift->shiftType->name
            . ' from ' . $shift->start->format('Y-m-d H:i')
            . ' to ' . $shift->end->format('Y-m-d H:i')
        );
        success(__('Shift deleted.'));
        throw_redirect(page_link_to('user_shifts'));
    }

    return page_with_title(shifts_title(), [
        error(sprintf(
            __('Do you want to delete the shift %s from %s to %s?'),
            $shift->shiftType->name,
            $shift->start->format(__('Y-m-d H:i')),
            $shift->end->format(__('H:i'))
        ), true),
        form([
            form_hidden('delete_shift', $shift->id),
            form_submit('delete', __('delete')),
        ]),
    ]);
}

/**
 * @return array
 */
function shift_controller()
{
    $user = auth()->user();
    $request = request();

    if (!auth()->can('user_shifts')) {
        throw_redirect(page_link_to('/'));
    }

    if (!$request->has('shift_id')) {
        throw_redirect(page_link_to('user_shifts'));
    }

    $shift = Shift($request->input('shift_id'));
    if (empty($shift)) {
        error(__('Shift could not be found.'));
        throw_redirect(page_link_to('user_shifts'));
    }

    $shifttype = $shift->shiftType;
    $room = $shift->room;
    /** @var AngelType[] $angeltypes */
    $angeltypes = AngelType::all();
    $user_shifts = Shifts_by_user($user->id);

    $shift_signup_state = new ShiftSignupState(ShiftSignupStatus::OCCUPIED, 0);
    foreach ($angeltypes as $angeltype) {
        $needed_angeltype = NeededAngeltype_by_Shift_and_Angeltype($shift, $angeltype);
        if (empty($needed_angeltype)) {
            continue;
        }

        $shift_entries = $shift->shiftEntries()
            ->where('angel_type_id', $angeltype->id)
            ->get();
        $needed_angeltype = (new AngelType())->forceFill($needed_angeltype);

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
        $angeltype->shift_signup_state = $angeltype_signup_state;
    }

    return [
        htmlspecialchars($shift->shiftType->name),
        Shift_view($shift, $shifttype, $room, $angeltypes, $shift_signup_state),
    ];
}

/**
 * @return array
 */
function shifts_controller()
{
    $request = request();
    if (!$request->has('action')) {
        throw_redirect(page_link_to('user_shifts'));
    }

    return match ($request->input('action')) {
        'view' => shift_controller(),
        'next' => shift_next_controller(), // throws redirect
        default => throw_redirect(page_link_to('/')),
    };
}

/**
 * Redirects the user to his next shift.
 */
function shift_next_controller()
{
    if (!auth()->can('user_shifts')) {
        throw_redirect(page_link_to('/'));
    }

    $upcoming_shifts = ShiftEntries_upcoming_for_user(auth()->user());

    if (!$upcoming_shifts->isEmpty()) {
        throw_redirect(shift_link($upcoming_shifts[0]->shift));
    }

    throw_redirect(page_link_to('user_shifts'));
}
