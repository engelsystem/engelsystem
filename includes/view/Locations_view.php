<?php

use Engelsystem\Helpers\Markdown;
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
        $description .= (new Markdown())->render($location->description);
    }

    $neededAngelTypes = '';
    if (auth()->can('admin_shifts') && $location->neededAngelTypes->isNotEmpty()) {
        $neededAngelTypes .= '<h3>' . __('location.required_angels') . '</h3><ul>';
        foreach ($location->neededAngelTypes as $neededAngelType) {
            if ($neededAngelType->count) {
                $neededAngelTypes .= '<li><a href="'
                    . url('angeltypes', ['action' => 'view', 'angeltype_id' => $neededAngelType->angelType->id])
                    . '">' . $neededAngelType->angelType->name
                    . '</a>: '
                    . $neededAngelType->count
                    . '</li>';
            }
        }
        $neededAngelTypes .= '</ul>';
    }

    $dect = '';
    if (config('enable_dect') && $location->dect) {
        $dect = heading(__('Contact'), 3)
            . description([__('general.dect') => sprintf(
                '<a href="tel:%s">%1$s</a>',
                htmlspecialchars($location->dect)
            )]);
    }

    $tabs = [];
    if ($location->map_url) {
        $tabs[__('location.map_url')] = sprintf(
            '<div class="map">'
            . '<iframe style="width: 100%%; min-height: 75vh; border: 0 none;" src="%s"></iframe>'
            . '</div>',
            htmlspecialchars($location->map_url)
        );
    }

    $tabs[__('general.shifts')] = div('first', [
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

    $link = button(url('/locations'), icon('chevron-left'), 'btn-sm', '', __('general.back'));
    return page_with_title(
        $link .
        icon('pin-map-fill') . htmlspecialchars($location->name),
        [
        $assignNotice,
        auth()->can('locations.edit') ? buttons([
            button(
                url('/admin/locations/edit/' . $location->id),
                icon('pencil'),
                '',
                '',
                __('form.edit')
            ),
        ]) : '',
        $dect,
        $description,
        $neededAngelTypes,
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
    if (auth()->can('locations.view')) {
        return '<a href="' . location_link($location) . '">'
            . icon('pin-map-fill') . htmlspecialchars($location->name)
            . '</a>';
    }

    return icon('pin-map-fill') . htmlspecialchars($location->name);
}
