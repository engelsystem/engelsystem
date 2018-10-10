<?php

use Engelsystem\ShiftsFilter;
use Engelsystem\ShiftsFilterRenderer;

/**
 * Text for Angeltype related links.
 *
 * @return string
 */
function angeltypes_title()
{
    return __('Angeltypes');
}

/**
 * Route angeltype actions.
 *
 * @return array
 */
function angeltypes_controller()
{
    $action = strip_request_item('action', 'list');

    switch ($action) {
        case 'view':
            return angeltype_controller();
        case 'edit':
            return angeltype_edit_controller();
        case 'delete':
            return angeltype_delete_controller();
        case 'about':
            return angeltypes_about_controller();
        case 'list':
        default:
            return angeltypes_list_controller();
    }
}

/**
 * Path to angeltype view.
 *
 * @param int   $angeltype_id AngelType id
 * @param array $params       additional params
 * @return string
 */
function angeltype_link($angeltype_id, $params = [])
{
    $params = array_merge(['action' => 'view', 'angeltype_id' => $angeltype_id], $params);
    return page_link_to('angeltypes', $params);
}

/**
 * Job description for all angeltypes (public to everyone)
 *
 * @return array
 */
function angeltypes_about_controller()
{
    $user = auth()->user();

    if ($user) {
        $angeltypes = AngelTypes_with_user($user->id);
    } else {
        $angeltypes = AngelTypes();
    }

    return [
        __('Teams/Job description'),
        AngelTypes_about_view($angeltypes, (bool)$user)
    ];
}

/**
 * Delete an Angeltype.
 *
 * @return array
 */
function angeltype_delete_controller()
{
    global $privileges;

    if (!in_array('admin_angel_types', $privileges)) {
        redirect(page_link_to('angeltypes'));
    }

    $angeltype = load_angeltype();

    if (request()->has('confirmed')) {
        AngelType_delete($angeltype);
        success(sprintf(__('Angeltype %s deleted.'), AngelType_name_render($angeltype)));
        redirect(page_link_to('angeltypes'));
    }

    return [
        sprintf(__('Delete angeltype %s'), $angeltype['name']),
        AngelType_delete_view($angeltype)
    ];
}

/**
 * Change an Angeltype.
 *
 * @return array
 */
function angeltype_edit_controller()
{
    global $privileges;

    // In supporter mode only allow to modify description
    $supporter_mode = !in_array('admin_angel_types', $privileges);
    $request = request();

    if ($request->has('angeltype_id')) {
        // Edit existing angeltype
        $angeltype = load_angeltype();

        if (!User_is_AngelType_supporter(auth()->user(), $angeltype)) {
            redirect(page_link_to('angeltypes'));
        }
    } else {
        // New angeltype
        if ($supporter_mode) {
            // Supporters aren't allowed to create new angeltypes.
            redirect(page_link_to('angeltypes'));
        }
        $angeltype = AngelType_new();
    }

    if ($request->has('submit')) {
        $valid = true;

        if (!$supporter_mode) {
            if ($request->has('name')) {
                $result = AngelType_validate_name($request->postData('name'), $angeltype);
                $angeltype['name'] = $result->getValue();
                if (!$result->isValid()) {
                    $valid = false;
                    error(__('Please check the name. Maybe it already exists.'));
                }
            }

            $angeltype['restricted'] = $request->has('restricted');
            $angeltype['no_self_signup'] = $request->has('no_self_signup');
            $angeltype['show_on_dashboard'] = $request->has('show_on_dashboard');

            $angeltype['requires_driver_license'] = $request->has('requires_driver_license');
        }

        $angeltype['description'] = strip_request_item_nl('description', $angeltype['description']);

        $angeltype['contact_name'] = strip_request_item('contact_name', $angeltype['contact_name']);
        $angeltype['contact_dect'] = strip_request_item('contact_dect', $angeltype['contact_dect']);
        $angeltype['contact_email'] = strip_request_item('contact_email', $angeltype['contact_email']);

        if ($valid) {
            if (!empty($angeltype['id'])) {
                AngelType_update($angeltype);
            } else {
                $angeltype = AngelType_create($angeltype);
            }

            success('Angel type saved.');
            redirect(angeltype_link($angeltype['id']));
        }
    }

    return [
        sprintf(__('Edit %s'), $angeltype['name']),
        AngelType_edit_view($angeltype, $supporter_mode)
    ];
}

/**
 * View details of a given angeltype.
 *
 * @return array
 */
function angeltype_controller()
{
    global $privileges;
    $user = auth()->user();

    if (!in_array('angeltypes', $privileges)) {
        redirect(page_link_to('/'));
    }

    $angeltype = load_angeltype();
    $user_angeltype = UserAngelType_by_User_and_AngelType($user->id, $angeltype);
    $user_driver_license = UserDriverLicense($user->id);
    $members = Users_by_angeltype($angeltype);

    $days = angeltype_controller_shiftsFilterDays($angeltype);
    $shiftsFilter = angeltype_controller_shiftsFilter($angeltype, $days);

    $shiftsFilterRenderer = new ShiftsFilterRenderer($shiftsFilter);
    $shiftsFilterRenderer->enableDaySelection($days);

    $shiftCalendarRenderer = shiftCalendarRendererByShiftFilter($shiftsFilter);
    $request = request();
    $tab = 0;

    if ($request->has('shifts_filter_day')) {
        $tab = 1;
    }

    return [
        sprintf(__('Team %s'), $angeltype['name']),
        AngelType_view(
            $angeltype,
            $members,
            $user_angeltype,
            in_array('admin_user_angeltypes', $privileges) || $user_angeltype['supporter'],
            in_array('admin_angel_types', $privileges),
            $user_angeltype['supporter'],
            $user_driver_license,
            $user,
            $shiftsFilterRenderer,
            $shiftCalendarRenderer,
            $tab
        )
    ];
}

/**
 * On which days do shifts for this angeltype occur? Needed for shiftCalendar.
 *
 * @param array $angeltype
 * @return array
 */
function angeltype_controller_shiftsFilterDays($angeltype)
{
    $all_shifts = Shifts_by_angeltype($angeltype);
    $days = [];
    foreach ($all_shifts as $shift) {
        $day = date('Y-m-d', $shift['start']);
        if (!in_array($day, $days)) {
            $days[] = $day;
        }
    }
    return $days;
}

/**
 * Sets up the shift filter for the angeltype.
 *
 * @param array $angeltype
 * @param array $days
 * @return ShiftsFilter
 */
function angeltype_controller_shiftsFilter($angeltype, $days)
{
    global $privileges;

    $request = request();
    $shiftsFilter = new ShiftsFilter(
        in_array('user_shifts_admin', $privileges),
        Room_ids(),
        [$angeltype['id']]
    );
    $selected_day = date('Y-m-d');
    if (!empty($days)) {
        $selected_day = $days[0];
    }
    if ($request->has('shifts_filter_day')) {
        $selected_day = $request->input('shifts_filter_day');
    }
    $shiftsFilter->setStartTime(parse_date('Y-m-d H:i', $selected_day . ' 00:00'));
    $shiftsFilter->setEndTime(parse_date('Y-m-d H:i', $selected_day . ' 23:59'));

    return $shiftsFilter;
}

/**
 * View a list of all angeltypes.
 *
 * @return array
 */
function angeltypes_list_controller()
{
    global $privileges;
    $user = auth()->user();

    if (!in_array('angeltypes', $privileges)) {
        redirect(page_link_to('/'));
    }

    $angeltypes = AngelTypes_with_user($user->id);

    foreach ($angeltypes as &$angeltype) {
        $actions = [
            button(
                page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]),
                __('view'),
                'btn-xs'
            )
        ];

        if (in_array('admin_angel_types', $privileges)) {
            $actions[] = button(
                page_link_to('angeltypes', ['action' => 'edit', 'angeltype_id' => $angeltype['id']]),
                __('edit'),
                'btn-xs'
            );
            $actions[] = button(
                page_link_to('angeltypes', ['action' => 'delete', 'angeltype_id' => $angeltype['id']]),
                __('delete'),
                'btn-xs'
            );
        }

        $angeltype['membership'] = AngelType_render_membership($angeltype);
        if (!empty($angeltype['user_angeltype_id'])) {
            $actions[] = button(
                page_link_to('user_angeltypes',
                    ['action' => 'delete', 'user_angeltype_id' => $angeltype['user_angeltype_id']]
                ),
                __('leave'),
                'btn-xs'
            );
        } else {
            $actions[] = button(
                page_link_to('user_angeltypes', ['action' => 'add', 'angeltype_id' => $angeltype['id']]),
                __('join'),
                'btn-xs'
            );
        }

        $angeltype['restricted'] = $angeltype['restricted'] ? glyph('lock') : '';
        $angeltype['no_self_signup'] = $angeltype['no_self_signup'] ? '' : glyph('share');

        $angeltype['name'] = '<a href="'
            . page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']])
            . '">'
            . $angeltype['name']
            . '</a>';

        $angeltype['actions'] = table_buttons($actions);
    }

    return [
        angeltypes_title(),
        AngelTypes_list_view($angeltypes, in_array('admin_angel_types', $privileges))
    ];
}

/**
 * Loads an angeltype from given angeltype_id request param.
 *
 * @return array
 */
function load_angeltype()
{
    $request = request();
    if (!$request->has('angeltype_id')) {
        redirect(page_link_to('angeltypes'));
    }

    $angeltype = AngelType($request->input('angeltype_id'));
    if (empty($angeltype)) {
        error(__('Angeltype doesn\'t exist . '));
        redirect(page_link_to('angeltypes'));
    }

    return $angeltype;
}
