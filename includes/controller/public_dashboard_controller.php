<?php

use Engelsystem\Models\Room;

/**
 * Loads all data for the public dashboard
 *
 * @return array
 */
function public_dashboard_controller()
{
    $stats = [
        'needed-3-hours' => stats_angels_needed_three_hours(),
        'needed-night'   => stats_angels_needed_for_nightshifts(),
        'angels-working' => stats_currently_working(),
        'hours-to-work'  => stats_hours_to_work()
    ];

    $free_shifts_source = Shifts_free(time(), time() + 12 * 60 * 60);
    $free_shifts = [];
    foreach ($free_shifts_source as $shift) {
        $free_shift = public_dashboard_controller_free_shift($shift);
        if (count($free_shift['needed_angels']) > 0) {
            $free_shifts[] = $free_shift;
        }
    }

    return [
        __('Public Dashboard'),
        public_dashboard_view($stats, $free_shifts)
    ];
}

/**
 * Gathers information for free shifts to display.
 *
 * @param array $shift
 * @return array
 */
function public_dashboard_controller_free_shift($shift)
{
    $shifttype = ShiftType($shift['shifttype_id']);
    $room = Room::find($shift['RID']);

    $free_shift = [
        'SID'            => $shift['SID'],
        'style'          => 'default',
        'start'          => date('H:i', $shift['start']),
        'end'            => date('H:i', $shift['end']),
        'duration'       => round(($shift['end'] - $shift['start']) / 3600),
        'shifttype_name' => $shifttype['name'],
        'title'          => $shift['title'],
        'room_name'      => $room->name,
        'needed_angels'  => []
    ];

    if (time() + 3 * 60 * 60 > $shift['start']) {
        $free_shift['style'] = 'warning';
    }
    if (time() > $shift['start']) {
        $free_shift['style'] = 'danger';
    }

    $free_shift['needed_angels'] = public_dashboard_needed_angels($shift['NeedAngels']);

    return $free_shift;
}

/**
 * Gathers information for needed angels on dashboard
 *
 * @param array $needed_angels
 * @return array
 */
function public_dashboard_needed_angels($needed_angels)
{
    $result = [];
    foreach ($needed_angels as $needed_angel) {
        $need = $needed_angel['count'] - $needed_angel['taken'];
        if ($need > 0) {
            $angeltype = AngelType($needed_angel['TID']);
            if ($angeltype['show_on_dashboard']) {
                $result[] = [
                    'need'           => $need,
                    'angeltype_name' => $angeltype['name']
                ];
            }
        }
    }
    return $result;
}

/**
 * Returns url to public dashboard
 *
 * @return string
 */
function public_dashboard_link()
{
    return page_link_to('public-dashboard');
}
