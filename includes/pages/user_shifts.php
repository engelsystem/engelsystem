<?php

use Engelsystem\Database\Db;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Room;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\UserAngelType;
use Engelsystem\ShiftsFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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

    if (auth()->user()->isFreeloader()) {
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
 * @param ShiftsFilter $shiftsFilter The shifts filter to update from request data
 * @param boolean      $user_shifts_admin Has the user user_shift_admin privilege?
 * @param string[]     $days An array of available filter days
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
function load_rooms(bool $onlyWithActiveShifts = false)
{
    $rooms = Room::orderBy('name');

    if ($onlyWithActiveShifts) {
        $roomIdsFromAngelType = NeededAngelType::query()
            ->whereNotNull('room_id')
            ->select('room_id');

        $roomIdsFromShift = Shift::query()
            ->leftJoin('needed_angel_types', 'shifts.id', 'needed_angel_types.shift_id')
            ->whereNotNull('needed_angel_types.shift_id')
            ->select('shifts.room_id');

        $rooms->whereIn('id', $roomIdsFromAngelType)
            ->orWhereIn('id', $roomIdsFromShift);
    }

    $rooms = $rooms->get();

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
                SELECT DISTINCT DATE(`start`) AS `id`, DATE(`start`) AS `name`
                FROM `shifts`
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

    if (!AngelType::count()) {
        error(__('The administration has not configured any angeltypes yet - or you are not subscribed to any angeltype.'));
        throw_redirect(page_link_to('/'));
    }

    $types = Db::select(
        '
            SELECT
                `angel_types`.`id`,
                `angel_types`.`name`,
                (
                    `angel_types`.`restricted`=0
                    OR (
                        NOT `user_angel_type`.`confirm_user_id` IS NULL
                        OR `user_angel_type`.`id` IS NULL
                    )
                ) AS `enabled`
            FROM `angel_types`
            LEFT JOIN `user_angel_type`
                ON (
                    `user_angel_type`.`angel_type_id`=`angel_types`.`id`
                    AND `user_angel_type`.`user_id`=?
                )
            ORDER BY `angel_types`.`name`
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
    return AngelType::whereRestricted(0)->get(['id', 'name'])->toArray();
}

/**
 * @return string
 */
function view_user_shifts()
{
    $user = auth()->user();

    $session = session();
    $days = load_days();
    $rooms = load_rooms(true);
    $types = load_types();
    $ownAngelTypes = [];

    /** @var EloquentCollection|UserAngelType[] $userAngelTypes */
    $userAngelTypes = UserAngelType::whereUserId($user->id)
        ->leftJoin('angel_types', 'user_angel_type.angel_type_id', 'angel_types.id')
        ->where(function (Builder $query) {
            $query->whereNotNull('user_angel_type.confirm_user_id')
                ->orWhere('angel_types.restricted', false);
        })
        ->get();
    foreach ($userAngelTypes as $type) {
        $ownAngelTypes[] = $type->angel_type_id;
    }

    if (!$session->has('shifts-filter')) {
        $room_ids = $rooms->pluck('id')->toArray();
        $shiftsFilter = new ShiftsFilter(auth()->can('user_shifts_admin'), $room_ids, $ownAngelTypes);
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
            'name' => __('occupied'),
        ],
        [
            'id'   => '0',
            'name' => __('free'),
        ],
    ];
    $start_day = $shiftsFilter->getStart()->format('Y-m-d');
    $start_time = $shiftsFilter->getStart()->format('H:i');
    $end_day = $shiftsFilter->getEnd()->format('Y-m-d');
    $end_time = $shiftsFilter->getEnd()->format('H:i');

    if (config('signup_requires_arrival') && !$user->state->arrived) {
        info(render_user_arrived_hint());
    }

    $formattedDays = collect($days)->map(function ($value) {
        return Carbon::make($value)->format(__('Y-m-d'));
    })->toArray();

    return page([
        div('col-md-12', [
            msg(),
            view(__DIR__ . '/../../resources/views/pages/user-shifts.html', [
                'title'         => shifts_title(),
                'room_select'   => make_select(
                    $rooms,
                    $shiftsFilter->getRooms(),
                    'rooms',
                    icon('pin-map-fill') . __('Rooms')
                ),
                'start_select'  => html_select_key(
                    'start_day',
                    'start_day',
                    array_combine($days, $formattedDays),
                    $start_day
                ),
                'start_time'    => $start_time,
                'end_select'    => html_select_key(
                    'end_day',
                    'end_day',
                    array_combine($days, $formattedDays),
                    $end_day
                ),
                'end_time'      => $end_time,
                'type_select'   => make_select(
                    $types,
                    $shiftsFilter->getTypes(),
                    'types',
                    icon('person-lines-fill') . __('Angeltypes') . '<sup>1</sup>',
                    $ownAngelTypes
                ),
                'filled_select' => make_select(
                    $filled,
                    $shiftsFilter->getFilled(),
                    'filled',
                    icon('person-fill-slash') . __('Occupancy')
                ),
                'task_notice'   =>
                    '<sup>1</sup>'
                    . __('The tasks shown here are influenced by the angeltypes you joined already!')
                    . ' <a href="' . url('/angeltypes/about') . '">'
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
                ),
            ]),
        ]),
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
    if (!auth()->can('ical')) {
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
 * @param int[]  $ownSelect
 * @return string
 */
function make_select($items, $selected, $name, $title = null, $ownSelect = [])
{
    $html = '';
    if (isset($title)) {
        $html .= '<h4>' . $title . '</h4>' . "\n";
    }

    $buttons = [
        button_checkbox_selection($name, __('All'), 'true'),
        button_checkbox_selection($name, __('None'), 'false'),
    ];
    if (count($ownSelect) > 0) {
        $buttons[] = button_checkbox_selection($name, __('Own'), json_encode($ownSelect));
    }

    $html .= buttons($buttons);
    $html .= '<div id="selection_' . $name . '" class="mb-3 selection ' . $name . '">' . "\n";

    $htmlItems = [];
    foreach ($items as $i) {
        $id = $name . '_' . $i['id'];
        $htmlItems[] = '<div class="form-check">'
            . '<input class="form-check-input" type="checkbox" id="' . $id . '" name="' . $name . '[]" value="' . $i['id'] . '" '
            . (in_array($i['id'], $selected) ? ' checked="checked"' : '')
            . '><label class="form-check-label" for="' . $id . '">' . $i['name'] . '</label>'
            . (!isset($i['enabled']) || $i['enabled'] ? '' : icon('mortarboard-fill'))
            . '</div>';
    }
    $html .= implode("\n", $htmlItems);

    $html .= '</div>' . "\n";
    $html .= buttons($buttons);

    return $html;
}
