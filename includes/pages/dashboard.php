<?php

function getTitle()
{
    return 'Dashboard';
}

function get_dashboard()
{
    $viewData = array(
        'number_upcoming_shifts' => 10,
        'number_upcoming_night_shifts' => 11,
        'number_currently_working' => 12,
        'number_hours_worked' => 13,
    );

    return  dashboardView($viewData);
}
