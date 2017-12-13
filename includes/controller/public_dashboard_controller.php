<?php

/**
 * Loads all data for the public dashboard
 */
function public_dashboard_controller()
{
    $stats = [
        'needed-3-hours' => stats_angels_needed_three_hours(),
        'needed-night' => stats_angels_needed_for_nightshifts(),
        'angels-working' => stats_currently_working(),
        'hours-to-work' => stats_hours_to_work()
    ];
    
    $free_shifts = Shifts_free(time(), time() + 12 * 60 * 60);
    
    return [
        _('Engelsystem Public Dashboard'),
        public_dashboard_view($stats, $free_shifts)
    ];
}

/**
 * Returns url to public dashboard
 */
function public_dashboard_link()
{
    return page_link_to('public-dashboard');
}
?>