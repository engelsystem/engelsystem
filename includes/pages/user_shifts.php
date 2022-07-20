<?php

use Engelsystem\Database\Db;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Models\Room;
use Engelsystem\ShiftsFilter;
use Illuminate\Support\Collection;

/**
 * @return string
 */
function shifts_title()
{
    return __('Shifts');
}

/**
 * Start different controllers for deleting shifts and shift_entries, edit shifts and add shift entries.
 * FIXME:
 * Transform into shift controller and shift entry controller.
 * Split actions into shift edit, shift delete, shift entry edit, shift entry delete
 * Introduce simpler and beautiful actions for shift entry join/leave for users
 *
 * @return string
 */
function user_shifts()
{
    $request = request();

    if (User_is_freeloader(auth()->user())) {
        throw_redirect(page_link_to('user_myshifts'));
    }

    if ($request->has('edit_shift')) {
        return shift_edit_controller();
    } elseif ($request->has('delete_shift')) {
        return shift_delete_controller();
    }
    return view_user_shifts();
}

/**
 * Helper function that updates the start and end time from request data.
 * Use update_ShiftsFilter().
 *
 * @param ShiftsFilter $shiftsFilter The shiftfilter to update.
 * @param string[]     $days
 */
function update_ShiftsFilter_timerange(ShiftsFilter $shiftsFilter, $days)
{
    $start_time = $shiftsFilter->getStartTime();
    if (is_null($start_time)) {
        $now = (new DateTime())->format('Y-m-d');
        $first_day = DateTime::createFromFormat(
            'Y-m-d',
            in_array($now, $days) ? $now : ($days[0] ?? (new DateTime())->format('Y-m-d'))
        )->getTimestamp();
        if (time() < $first_day) {
            $start_time = $first_day;
        } else {
            $start_time = time();
        }
    }

    $end_time = $shiftsFilter->getEndTime();
    if (is_null($end_time)) {
        $end_time = $start_time + 24 * 60 * 60;
        $end = Carbon::createFromTimestamp($end_time);
        if (!in_array($end->format('Y-m-d'), $days)) {
            $end->startOfDay()->subSecond(); // the day before
            $end_time = $end->timestamp;
        }
    }

    $shiftsFilter->setStartTime(check_request_datetime(
        'start_day',
        'start_time',
        $days,
        $start_time
    ));
    $shiftsFilter->setEndTime(check_request_datetime(
        'end_day',
        'end_time',
        $days,
        $end_time
    ));

    if ($shiftsFilter->getStartTime() > $shiftsFilter->getEndTime()) {
        $shiftsFilter->setEndTime($shiftsFilter->getStartTime() + 24 * 60 * 60);
    }
}

/**
 * Update given ShiftsFilter with filter params from user input
 *
 * @param ShiftsFilter $shiftsFilter      The shifts filter to update from request data
 * @param boolean      $user_shifts_admin Has the user user_shift_admin privilege?
 * @param string[]     $days              An array of available filter days
 */
function update_ShiftsFilter(ShiftsFilter $shiftsFilter, $user_shifts_admin, $days)
{
    $shiftsFilter->setUserShiftsAdmin($user_shifts_admin);
    $shiftsFilter->setFilled(check_request_int_array('filled', $shiftsFilter->getFilled()));
    $shiftsFilter->setRooms(check_request_int_array('rooms', $shiftsFilter->getRooms()));
    $shiftsFilter->setTypes(check_request_int_array('types', $shiftsFilter->getTypes()));
    update_ShiftsFilter_timerange($shiftsFilter, $days);
}

/**
 * @return Room[]|Collection
 */
function load_rooms()
{
    $rooms = Rooms();
    if ($rooms->isEmpty()) {
        error(__('The administration has not configured any rooms yet.'));
        throw_redirect(page_link_to('/'));
    }

    return $rooms;
}

/**
 * @return array
 */
function load_days()
{
    $days = (new Collection(Db::select(
        '
                SELECT DISTINCT DATE(FROM_UNIXTIME(`start`)) AS `id`, DATE(FROM_UNIXTIME(`start`)) AS `name`
                FROM `Shifts`
                ORDER BY `id`, `name`
            '
    )))
        ->pluck('id')
        ->toArray();

    if (empty($days)) {
        error(__('The administration has not configured any shifts yet.'));
        // Do not try to redirect to the current page
        if (config('home_site') != 'user_shifts') {
            throw_redirect(page_link_to('/'));
        }
    }
    return $days;
}

/**
 * @return array[]|false
 */
function load_types()
{
    $user = auth()->user();

    if (!count(Db::select('SELECT `id`, `name` FROM `AngelTypes`'))) {
        error(__('The administration has not configured any angeltypes yet - or you are not subscribed to any angeltype.'));
        throw_redirect(page_link_to('/'));
    }
    $types = Db::select('
            SELECT
                `AngelTypes`.`id`,
                `AngelTypes`.`name`,
                (
                    `AngelTypes`.`restricted`=0
                    OR (
                        NOT `UserAngelTypes`.`confirm_user_id` IS NULL
                        OR `UserAngelTypes`.`id` IS NULL
                    )
                ) AS `enabled`
            FROM `AngelTypes`
            LEFT JOIN `UserAngelTypes`
                ON (
                    `UserAngelTypes`.`angeltype_id`=`AngelTypes`.`id`
                    AND `UserAngelTypes`.`user_id`=?
                )
            ORDER BY `AngelTypes`.`name`
        ',
        [
            $user->id,
        ]
    );
    if (empty($types)) {
        return unrestricted_angeltypes();
    }
    return $types;
}

/**
 * @return array[]
 */
function unrestricted_angeltypes()
{
    return Db::select('SELECT `id`, `name` FROM `AngelTypes` WHERE `restricted` = 0');
}

/**
 * @return string
 */
function view_user_shifts()
{
    $user = auth()->user();

    $session = session();
    $days = load_days();
    $rooms = load_rooms();
    $types = load_types();
    $ownTypes = [];

    foreach (UserAngelTypes_by_User($user->id, true) as $type) {
        if (!$type['confirm_user_id'] && $type['restricted']) {
            continue;
        }

        $ownTypes[] = (int)$type['angeltype_id'];
    }

    if (!$session->has('shifts-filter')) {
        $room_ids = $rooms->pluck('id')->toArray();
        $shiftsFilter = new ShiftsFilter(auth()->can('user_shifts_admin'), $room_ids, $ownTypes);
        $session->set('shifts-filter', $shiftsFilter->sessionExport());
    }

    $shiftsFilter = new ShiftsFilter();
    $shiftsFilter->sessionImport($session->get('shifts-filter'));
    update_ShiftsFilter($shiftsFilter, auth()->can('user_shifts_admin'), $days);
    $session->set('shifts-filter', $shiftsFilter->sessionExport());

    $shiftCalendarRenderer = shiftCalendarRendererByShiftFilter($shiftsFilter);

    if (empty($user->api_key)) {
        User_reset_api_key($user, false);
    }

    $filled = [
        [
            'id'   => '1',
            'name' => __('occupied')
        ],
        [
            'id'   => '0',
            'name' => __('free')
        ]
    ];
    $start_day = date('Y-m-d', $shiftsFilter->getStartTime());
    $start_time = date('H:i', $shiftsFilter->getStartTime());
    $end_day = date('Y-m-d', $shiftsFilter->getEndTime());
    $end_time = date('H:i', $shiftsFilter->getEndTime());

    if (config('signup_requires_arrival') && !$user->state->arrived) {
        info(render_user_arrived_hint());
    }

    return page([
        div('col-md-12', [
            msg(),
            view(__DIR__ . '/../../resources/views/pages/user-shifts.html', [
                'title'         => shifts_title(),
                'room_select'   => make_select($rooms, $shiftsFilter->getRooms(), 'rooms', __('Rooms')),
                'start_select'  => html_select_key(
                    'start_day',
                    'start_day',
                    array_combine($days, $days),
                    $start_day
                ),
                'start_time'    => $start_time,
                'end_select'    => html_select_key(
                    'end_day',
                    'end_day',
                    array_combine($days, $days),
                    $end_day
                ),
                'end_time'      => $end_time,
                'type_select'   => make_select(
                    $types,
                    $shiftsFilter->getTypes(),
                    'types',
                    __('Angeltypes') . '<sup>1</sup>',
                    [
                        button(
                            'javascript: checkOwnTypes(\'selection_types\', ' . json_encode($ownTypes) . ')',
                            __('Own'),
                            'd-print-none'
                        ),
                    ]
                ),
                'filled_select' => make_select($filled, $shiftsFilter->getFilled(), 'filled', __('Occupancy')),
                'task_notice'   =>
                    '<sup>1</sup>'
                    . __('The tasks shown here are influenced by the angeltypes you joined already!')
                    . ' <a href="' . page_link_to('angeltypes', ['action' => 'about']) . '">'
                    . __('Description of the jobs.')
                    . '</a>',
                'shifts_table'  => msg() . $shiftCalendarRenderer->render(),
                'ical_text'     => div('mt-3', ical_hint()),
                'filter'        => __('Filter'),
                'filter_toggle' => __('shifts.filter.toggle'),
                'set_yesterday' => __('Yesterday'),
                'set_today'     => __('Today'),
                'set_tomorrow'  => __('Tomorrow'),
                'set_last_8h'   => __('last 8h'),
                'set_last_4h'   => __('last 4h'),
                'set_next_4h'   => __('next 4h'),
                'set_next_8h'   => __('next 8h'),
                'buttons'       => button(
                    public_dashboard_link(),
                    icon('speedometer2') . __('Public Dashboard')
                )
            ])
        ])
    ]);
}

/**
 * Returns a hint for the user how the ical feature works.
 *
 * @return string
 */
function ical_hint()
{
    $user = auth()->user();
    if(!auth()->can('ical')) {
        return '';
    }

    return heading(__('iCal export and API') . ' ' . button_help('user/ical'), 2)
        . '<p>' . sprintf(
            __('Export your own shifts. <a href="%s">iCal format</a> or <a href="%s">JSON format</a> available (please keep secret, otherwise <a href="%s">reset the api key</a>).'),
            page_link_to('ical', ['key' => $user->api_key]),
            page_link_to('shifts_json_export', ['key' => $user->api_key]),
            page_link_to('user_myshifts', ['reset' => 1])
        )
        . ' <button class="btn btn-sm btn-danger" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapseApiKey"
            aria-expanded="false" aria-controls="collapseApiKey">
            ' . __('Show API Key') . '
            </button>'
        . '</p>'
        . '<p id="collapseApiKey" class="collapse"><code>' . $user->api_key . '</code></p>';
}

/**
 * @param array $array
 * @return array
 */
function get_ids_from_array($array)
{
    return $array['id'];
}

/**
 * @param array  $items
 * @param array  $selected
 * @param string $name
 * @param string $title
 * @param array  $additionalButtons
 * @return string
 */
function make_select($items, $selected, $name, $title = null, $additionalButtons = [])
{
    $html = '';
    $htmlItems = [];
    if (isset($title)) {
        $html .= '<h4>' . $title . '</h4>' . "\n";
    }

    $buttons = [];
    $buttons[] = button('javascript: checkAll(\'selection_' . $name . '\', true)', __('All'), 'd-print-none');
    $buttons[] = button('javascript: checkAll(\'selection_' . $name . '\', false)', __('None'), 'd-print-none');
    $buttons = array_merge($buttons, $additionalButtons);

    $html .= buttons($buttons);

    foreach ($items as $i) {
        $htmlItems[] = '<div class="checkbox">'
            . '<label><input type="checkbox" name="' . $name . '[]" value="' . $i['id'] . '" '
            . (in_array($i['id'], $selected) ? ' checked="checked"' : '')
            . ' > ' . $i['name'] . '</label>'
            . (!isset($i['enabled']) || $i['enabled'] ? '' : icon('book'))
            . '</div>';
    }
    $html .= '<div id="selection_' . $name . '" class="selection ' . $name . '">' . "\n";
    $html .= implode("\n", $htmlItems);

    $html .= buttons($buttons);

    $html .= '</div>' . "\n";
    return $html;
}
