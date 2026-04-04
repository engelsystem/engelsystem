<?php

namespace Engelsystem;

use Illuminate\Support\Str;

class ShiftsFilterRenderer
{
    /**
     * The shiftFilter to render.
     *
     * @var ShiftsFilter
     */
    private $shiftsFilter;

    /**
     * Should the filter display a day selection.
     *
     * @var boolean
     */
    private $daySelectionEnabled = false;

    /**
     * Days that can be selected.
     * Format Y-m-d
     *
     * @var string[]
     */
    private $days = [];

    /**
     * ShiftsFilterRenderer constructor.
     *
     * @param ShiftsFilter $shiftsFilter
     */
    public function __construct(ShiftsFilter $shiftsFilter)
    {
        $this->shiftsFilter = $shiftsFilter;
    }

    /**
     * Renders the filter.
     *
     * @param string $page_link Link pointing to the actual page.
     * @param array  $dashboardFilter
     *
     * @return string Generated HTML
     */
    public function render($page_link, $dashboardFilter = [])
    {
        $toolbar = [];
        if ($this->daySelectionEnabled && !empty($this->days)) {
            $selected_day = date('Y-m-d', $this->shiftsFilter->getStartTime());
            $selected_day_formatted = dateWithEventDay($selected_day);
            $day_dropdown_items = [];
            foreach ($this->days as $value => $day) {
                $link = $page_link . '&shifts_filter_day=' . $value;
                $day_dropdown_items[] = toolbar_dropdown_item($link, $day, Str::startsWith($day, $selected_day));
            }
            $toolbar[] = toolbar_dropdown($selected_day_formatted, $day_dropdown_items, true);

            if ($dashboardFilter) {
                $toolbar[] = button(
                    url('/public-dashboard', ['filtered' => true] + $dashboardFilter),
                    icon('speedometer2') . __('Dashboard')
                );
            }
            $showFilledShifts = in_array(ShiftsFilter::FILLED_FILLED, $this->shiftsFilter->getFilled());
            $toolbar[] = button(
                $page_link . '&showFilledShifts=' . (int) (!$showFilledShifts) . '&showShiftsTab=1&shifts_filter_day=' . request('shifts_filter_day', $selected_day),
                $showFilledShifts ? (icon('eye-slash') . __('Show free shifts')) : (icon('eye') . __('Show all shifts'))
            );
        }
        return div('mb-3', [
            toolbar_pills($toolbar, 'gap-2'),
        ]);
    }

    /**
     * Should the filter display a day selection.
     *
     * @param string[] $days
     */
    public function enableDaySelection($days)
    {
        $this->daySelectionEnabled = true;
        $this->days = $days;
    }
}
