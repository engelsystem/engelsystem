<?php

namespace Engelsystem;

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
            $selected_day_formatted = date(__('Y-m-d'), $this->shiftsFilter->getStartTime());
            $day_dropdown_items = [];
            foreach ($this->days as $value => $day) {
                $link = $page_link . '&shifts_filter_day=' . $value;
                $day_dropdown_items[] = toolbar_item_link($link, '', $day);
            }
            $toolbar[] = toolbar_dropdown($selected_day_formatted, $day_dropdown_items, true);

            if ($dashboardFilter) {
                $toolbar[] = sprintf(
                    '<li role="presentation"><a class="nav-link" href="%s">%s</a></li>',
                    url('/public-dashboard', ['filtered' => true] + $dashboardFilter),
                    icon('speedometer2') . __('Dashboard')
                );
            }
            if (!request('showFilledShifts') && !auth()->can('user_shifts_admin')) {
                $toolbar[] = sprintf(
                    '<li role="presentation"><a class="nav-link" href="%s">%s</a></li>',
                    $page_link . '&showFilledShifts=1&showShiftsTab=1&shifts_filter_day=' . request('shifts_filter_day', $selected_day),
                    icon('eye') . __('All')
                );
            }
        }
        return div('mb-3', [
            toolbar_pills($toolbar),
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

    /**
     * Should the filter display a day selection.
     *
     * @return bool
     */
    public function isDaySelectionEnabled()
    {
        return $this->daySelectionEnabled;
    }
}
