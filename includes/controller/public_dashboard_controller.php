<?php

use Engelsystem\Models\Room;
use Engelsystem\ShiftsFilter;

/**
 * Loads all data for the public dashboard
 *
 * @return array
 */
function public_dashboard_controller()
{
    $filter = null;
    if (request()->get('filtered')) {
        $requestRooms = check_request_int_array('rooms');
        $requestAngelTypes = check_request_int_array('types');

        if (!$requestRooms && !$requestAngelTypes) {
            $sessionFilter = collect(session()->get('shifts-filter', []));
            $requestRooms = $sessionFilter->get('rooms', []);
            $requestAngelTypes = $sessionFilter->get('types', []);
        }

        $angelTypes = collect(unrestricted_angeltypes());
        $rooms = $requestRooms ?: Rooms()->pluck('id')->toArray();
        $angelTypes = $requestAngelTypes ?: $angelTypes->pluck('id')->toArray();
        $filterValues = [
            'userShiftsAdmin' => false,
            'filled'          => [],
            'rooms'           => $rooms,
            'types'           => $angelTypes,
            'startTime'       => null,
            'endTime'         => null,
        ];

        $filter = new ShiftsFilter();
        $filter->sessionImport($filterValues);
    }

    $stats = [
        'needed-3-hours' => stats_angels_needed_three_hours($filter),
        'needed-night'   => stats_angels_needed_for_nightshifts($filter),
        'angels-working' => stats_currently_working($filter),
        'hours-to-work'  => stats_hours_to_work($filter)
    ];

    $free_shifts_source = Shifts_free(time(), time() + 12 * 60 * 60, $filter);
    $free_shifts = [];
    foreach ($free_shifts_source as $shift) {
        $free_shift = public_dashboard_controller_free_shift($shift, $filter);
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
 * @param array             $shift
 * @param ShiftsFilter|null $filter
 *
 * @return array
 */
function public_dashboard_controller_free_shift($shift, ShiftsFilter $filter = null)
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
        'needed_angels'  => public_dashboard_needed_angels($shift['NeedAngels'], $filter),
    ];

    if (time() + 3 * 60 * 60 > $shift['start']) {
        $free_shift['style'] = 'warning';
    }
    if (time() > $shift['start']) {
        $free_shift['style'] = 'danger';
    }

    return $free_shift;
}

/**
 * Gathers information for needed angels on dashboard
 *
 * @param array             $needed_angels
 * @param ShiftsFilter|null $filter
 *
 * @return array
 */
function public_dashboard_needed_angels($needed_angels, ShiftsFilter $filter = null)
{
    $result = [];
    foreach ($needed_angels as $needed_angel) {
        $need = $needed_angel['count'] - $needed_angel['taken'];
        if ($need > 0 && (!$filter || in_array($needed_angel['TID'], $filter->getTypes()))) {
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
 * @param array $parameters
 *
 * @return string
 */
function public_dashboard_link(array $parameters = []): string
{
    return page_link_to('public-dashboard', $parameters);
}
