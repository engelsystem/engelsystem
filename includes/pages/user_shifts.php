<?php

use Engelsystem\Database\DB;
use Engelsystem\ShiftsFilter;

/**
 * @return string
 */
function shifts_title()
{
    return _('Shifts');
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
    global $user;
    $request = request();

    if (User_is_freeloader($user)) {
        redirect(page_link_to('user_myshifts'));
    }

    if ($request->has('edit_shift')) {
        return shift_edit_controller();
    } elseif ($request->has('delete_shift')) {
        return shift_delete_controller();
    }

  // websql disabled by config
  if (config('enable_websql') != true) {
      return view_user_shifts();
  }

  // target for the noscript meta-redirect
  if ($request->has('nojs')) {
      setcookie('websql', 'nope');
      redirect(page_link_to('user_shifts'));
  }

  // ability to turn websql back on
  if ($request->has('websql')) {
      setcookie('websql', 'yes');
      redirect(page_link_to('user_shifts'));
  }

  // cookie is not set: display websql-version to let it check for websql support
  if (! isset($_COOKIE['websql'])) {
      return view_user_shifts_browser();

  // cookie is set and websql is supported: display websql-version
  } else if (isset($_COOKIE['websql']) && $_COOKIE['websql'] == 'yes') {
      return view_user_shifts_browser();

  // cookie is set and websql is not supported: display regular version
  } else {
      return view_user_shifts();
  }
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
    if ($start_time == null) {
        $start_time = time();
    }

    $end_time = $shiftsFilter->getEndTime();
    if ($end_time == null) {
        $end_time = $start_time + 24 * 60 * 60;
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
 * @return array
 */
function load_rooms()
{
    $rooms = DB::select(
        'SELECT `RID` AS `id`, `Name` AS `name` FROM `Room` ORDER BY `Name`'
    );
    if (empty($rooms)) {
        error(_('The administration has not configured any rooms yet.'));
        redirect(page_link_to('/'));
    }
    return $rooms;
}

/**
 * @return array
 */
function load_days()
{
    $days = DB::select('
      SELECT DISTINCT DATE(FROM_UNIXTIME(`start`)) AS `id`, DATE(FROM_UNIXTIME(`start`)) AS `name`
      FROM `Shifts`
      ORDER BY `id`, `name`
    ');
    $days = array_map('array_shift', $days);

    if (empty($days)) {
        error(_('The administration has not configured any shifts yet.'));
        redirect(page_link_to('/'));
    }
    return $days;
}

/**
 * @return array[]|false
 */
function load_types()
{
    global $user;

    if (!count(DB::select('SELECT `id`, `name` FROM `AngelTypes` WHERE `restricted` = 0'))) {
        error(_('The administration has not configured any angeltypes yet - or you are not subscribed to any angeltype.'));
        redirect(page_link_to('/'));
    }
    $types = DB::select('
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
            $user['UID'],
        ]
    );
    if (empty($types)) {
        return DB::select('SELECT `id`, `name` FROM `AngelTypes` WHERE `restricted` = 0');
    }
    return $types;
}

/**
 * @return string
 */
function view_user_shifts()
{
    global $user, $privileges, $ical_shifts;

    $session = session();
    $ical_shifts = [];
    $days = load_days();
    $rooms = load_rooms();
    $types = load_types();

    if (!$session->has('ShiftsFilter')) {
        $room_ids = [
            $rooms[0]['id']
        ];
        $type_ids = array_map('get_ids_from_array', $types);
        $shiftsFilter = new ShiftsFilter(in_array('user_shifts_admin', $privileges), $room_ids, $type_ids);
        $session->set('ShiftsFilter', $shiftsFilter);
    }

    $shiftsFilter = $session->get('ShiftsFilter');
    update_ShiftsFilter($shiftsFilter, in_array('user_shifts_admin', $privileges), $days);

    $shiftCalendarRenderer = shiftCalendarRendererByShiftFilter($shiftsFilter);

    if ($user['api_key'] == '') {
        User_reset_api_key($user, false);
    }

    $filled = [
        [
            'id'   => '1',
            'name' => _('occupied')
        ],
        [
            'id'   => '0',
            'name' => _('free')
        ]
    ];
    $start_day = date('Y-m-d', $shiftsFilter->getStartTime());
    $start_time = date('H:i', $shiftsFilter->getStartTime());
    $end_day = date('Y-m-d', $shiftsFilter->getEndTime());
    $end_time = date('H:i', $shiftsFilter->getEndTime());

    if (config('signup_requires_arrival') && !$user['Gekommen']) {
        info(render_user_arrived_hint());
    }

    return page([
        div('col-md-12', [
            msg(),
            view(__DIR__ . '/../../templates/user_shifts.html', [
                'title'         => shifts_title(),
                'room_select'   => make_select($rooms, $shiftsFilter->getRooms(), 'rooms', _('Rooms')),
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
                    _('Angeltypes') . '<sup>1</sup>'
                ),
                'filled_select' => make_select($filled, $shiftsFilter->getFilled(), 'filled', _('Occupancy')),
                'task_notice'   =>
                    '<sup>1</sup>'
                    . _('The tasks shown here are influenced by the angeltypes you joined already!')
                    . ' <a href="' . page_link_to('angeltypes', ['action' => 'about']) . '">'
                    . _('Description of the jobs.')
                    . '</a>',
                'shifts_table'  => msg() . $shiftCalendarRenderer->render(),
                'ical_text'     => '<h2>' . _('iCal export') . '</h2><p>' . sprintf(
                        _('Export of shown shifts. <a href="%s">iCal format</a> or <a href="%s">JSON format</a> available (please keep secret, otherwise <a href="%s">reset the api key</a>).'),
                        page_link_to('ical', ['key' => $user['api_key']]),
                        page_link_to('shifts_json_export', ['key' => $user['api_key']]),
                        page_link_to('user_myshifts', ['reset' => 1])
                    ) . '</p>',
                'filter'        => _('Filter'),
                'set_yesterday' => _('Yesterday'),
                'set_today'     => _('Today'),
                'set_tomorrow'  => _('Tomorrow'),
                'set_last_8h'   => _('last 8h'),
                'set_last_4h'   => _('last 4h'),
                'set_next_4h'   => _('next 4h'),
                'set_next_8h'   => _('next 8h'),
                'buttons'       => button(
                    public_dashboard_link(),
                    glyph('dashboard') . _('Public Dashboard')
                )
            ])
        ])
    ]);
}

/**
 * @param array $array
 * @return array
 */
function get_ids_from_array($array)
{
    return $array['id'];
}

function make_select($items, $selected, $name, $title = null)
{
    $html_items = [];
    if (isset($title)) {
        $html_items[] = '<h4>' . $title . '</h4>' . "\n";
    }

    foreach ($items as $i) {
        $html_items[] = '<div class="checkbox">'
            . '<label><input type="checkbox" name="' . $name . '[]" value="' . $i['id'] . '" '
            . (in_array($i['id'], $selected) ? ' checked="checked"' : '')
            . ' > ' . $i['name'] . '</label>'
            . (!isset($i['enabled']) || $i['enabled'] ? '' : glyph('lock'))
            . '</div><br />';
    }
    $html = '<div id="selection_' . $name . '" class="selection ' . $name . '">' . "\n";
    $html .= implode("\n", $html_items);
    $html .= buttons([
        button('javascript: checkAll(\'selection_' . $name . '\', true)', _('All'), ''),
        button('javascript: checkAll(\'selection_' . $name . '\', false)', _('None'), '')
    ]);
    $html .= '</div>' . "\n";
    return $html;
}
