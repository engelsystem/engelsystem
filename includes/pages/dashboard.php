<?php

function getDashboardTitle()
{
    return _('Dashboard');
}

function get_dashboard()
{
    $shifts = getAllShifts();

    $viewData = array(
        'number_upcoming_shifts' => getNumberUpcomingShifts($shifts, 3*60*60),
        'number_upcoming_night_shifts' => 11,
        'number_currently_working' => 12,
        'number_hours_worked' => 13,
        'jobs_currently_running' => getListCurrentShifts($shifts),
        'jobs_now' => getListUpcomingShifts($shifts, 60*60),
        'jobs_soon' => getListUpcomingShifts($shifts, 3*60*60),
        'news' => getAllNewsList(),
        'text_within_next_3_hours' => _("Within the next 3 hours"),
        'text_within_next_hour' => _("Within the next hour"),
        'text_currently_running' => _("Currently running"),
        'text_hours_to_be_worked' => _("Hours to be worked"),
        'text_currently_working' => _("Angels currently working"),
        'text_angels_needed_for_night_shifts' => _("Angels needed for night shifts"),
        'text_angels_needed_next_3_hours' => _("Angels needed in the next 3 hrs"),
        'text_news' => _("News"),
    );

    return  dashboardView($viewData);
}

/**
 * @param array $shifts
 * @param $withinSeconds
 *
 * @return int
 */
function getNumberUpcomingShifts($shifts, $withinSeconds)
{
    return count(getUpcomingShifts($shifts, $withinSeconds));
}

/**
 * @param $shifts
 * @param $withinSeconds
 *
 * @return string
 */
function getListUpcomingShifts($shifts, $withinSeconds)
{
    $upcomingShifts = getUpcomingShifts($shifts, $withinSeconds);

    return buildList($upcomingShifts);
}

/**
 * @param $shifts
 * @param $withinSeconds
 *
 * @return array
 */
function getUpcomingShifts($shifts, $withinSeconds)
{
    return array_filter($shifts, function ($shift) use ($withinSeconds) {
        $currentTime = time();

        return $shift['start'] > $currentTime && $shift['start'] <= ($currentTime + $withinSeconds);
    });
}

function getListCurrentShifts($shifts)
{
    $currentlyRunning = getCurrentShifts($shifts);

    return buildList($currentlyRunning);
}

/**
 * Filters the currently running shifs
 *
 * @param $shifts
 * @return array
 */
function getCurrentShifts($shifts)
{
    return array_filter($shifts, function ($shift) {
        $currentTime = time();

        return $currentTime >= $shift['start'] && $shift['end'] >= $currentTime;
    });
}

/**
 * Creates a li list out of shifts with its titles as labels.
 *
 * @param $shifts
 * @return string
 */
function buildList($shifts)
{
    $list = '<ul class="list-group">';
    foreach ($shifts as $shift) {
        $list .= sprintf("<li class='list-group-item'>%s</li>\n", $shift['title']);
    }

    return $list.'</ul>';
}

/**
 * Get all shift types.
 *
 * @return array
 */
function getAllShifts()
{
    return sql_select("SELECT * FROM `Shifts` ORDER BY `start`");
}

function getAllNewsList()
{
    $news = sql_select("SELECT * FROM `News` ORDER BY `Datum`");

    $list = '<ul class="list-group">';
    foreach ($news as $article) {
        $list .= sprintf("<li class='list-group-item'>%s</li> \n", $article['Betreff']);
    }

    return $list.'</ul>';
}
