<?php

use Engelsystem\Models\Location;
use Engelsystem\ShiftCalendarRenderer;
use Engelsystem\ShiftsFilterRenderer;

/**
 *
 * @param Location              $location
 * @param ShiftsFilterRenderer  $shiftsFilterRenderer
 * @param ShiftCalendarRenderer $shiftCalendarRenderer
 * @return string
 */
function location_view(Location $location, ShiftsFilterRenderer $shiftsFilterRenderer, ShiftCalendarRenderer $shiftCalendarRenderer)
{
    $user = auth()->user();

    $assignNotice = '';
    if (config('signup_requires_arrival') && !$user->state->arrived) {
        $assignNotice = info(render_user_arrived_hint(), true);
    }

    $description = '';
    if ($location->description) {
        $description = '<h3>' . __('general.description') . '</h3>';
        $parsedown = new Parsedown();
        $description .= $parsedown->parse($location->description);
    }

    $dect = '';
    if (config('enable_dect') && $location->dect) {
        $dect = heading(__('Contact'), 3)
            . description([__('general.dect') => sprintf('<a href="tel:%s">%1$s</a>', $location->dect)]);
    }

    $tabs = [];
    if ($location->map_url) {
        $tabs[__('location.map_url')] = sprintf(
            '<div class="map">'
            . '<iframe style="width: 100%%; min-height: 400px; border: 0 none;" src="%s"></iframe>'
            . '</div>',
            $location->map_url
        );
    }

    $tabs[__('Shifts')] = div('first', [
        $shiftsFilterRenderer->render(url('/locations', [
            'action'  => 'view',
            'location_id' => $location->id,
        ]), ['locations' => [$location->id]]),
        $shiftCalendarRenderer->render(),
    ]);

    $selected_tab = 0;
    $request = request();
    if ($request->has('shifts_filter_day')) {
        $selected_tab = count($tabs) - 1;
    }

    $link = button(url('/admin/locations'), icon('chevron-left'), 'btn-sm');
    return page_with_title(
        (auth()->can('admin_locations') ? $link . ' ' : '') .
        icon('pin-map-fill') . $location->name,
        [
        $assignNotice,
        auth()->can('admin_locations') ? buttons([
            button(
                url('/admin/locations/edit/' . $location->id),
                icon('pencil') . __('edit')
            ),
        ]) : '',
        $dect,
        $description,
        tabs($tabs, $selected_tab),
        ],
        true
    );
}

/**
 *
 * @param Location $location
 * @return string
 */
function location_name_render(Location $location)
{
    if (auth()->can('view_locations')) {
        return '<a href="' . location_link($location) . '">' . icon('pin-map-fill') . $location->name . '</a>';
    }

    return icon('pin-map-fill') . $location->name;
}
