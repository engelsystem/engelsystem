<?php

use Engelsystem\Controllers\Admin\LocationsController;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Location;
use Engelsystem\ShiftsFilter;
use Engelsystem\ShiftsFilterRenderer;

/**
 * Location controllers for managing everything location related.
 */

/**
 * View a location with its shifts.
 *
 * @return array
 */
function location_controller(): array
{
    if (!auth()->can('locations.view')) {
        throw_redirect(url('/'));
    }

    $request = request();
    $location = load_location();

    $days_list = Days_by_Location_id($location->id);
    $days = [];
    foreach ($days_list as $day) {
        if (!isset($days[$day])) {
            $days[$day] = dateWithEventDay($day);
        }
    }

    $shiftsFilter = new ShiftsFilter(
        true,
        [$location->id],
        AngelType::query()->get('id')->pluck('id')->toArray()
    );
    $selected_day = date('Y-m-d');
    if (!empty($days) && !isset($days[$selected_day])) {
        $selected_day = array_key_first($days);
    }
    if ($request->input('shifts_filter_day')) {
        $selected_day = $request->input('shifts_filter_day');
    }
    $shiftsFilter->setStartTime(parse_date('Y-m-d H:i', $selected_day . ' 00:00'));
    $shiftsFilter->setEndTime(parse_date('Y-m-d H:i', $selected_day . ' 23:59'));

    $shiftsFilterRenderer = new ShiftsFilterRenderer($shiftsFilter);
    $shiftsFilterRenderer->enableDaySelection($days);

    $shiftCalendarRenderer = shiftCalendarRendererByShiftFilter($shiftsFilter);

    return [
        htmlspecialchars($location->name),
        location_view($location, $shiftsFilterRenderer, $shiftCalendarRenderer),
    ];
}

/**
 * Dispatch different location actions.
 *
 * @return array
 */
function locations_controller(): array
{
    $request = request();
    $action = $request->input('action');

    return match ($action) {
        'view'  => location_controller(),
        'list'  => throw_redirect(url('/locations')),
        default => ['', app(LocationsController::class)->index()->getContent()],
    };
}

/**
 * @param Location $location
 * @return string
 */
function location_link(Location $location)
{
    return url('/locations', ['action' => 'view', 'location_id' => $location->id]);
}

/**
 * Loads location by request param location_id
 *
 * @return Location
 */
function load_location()
{
    if (!test_request_int('location_id')) {
        throw_redirect(url('/'));
    }

    $location = Location::find(request()->input('location_id'));
    if (!$location) {
        throw_redirect(url('/'));
    }

    return $location;
}
