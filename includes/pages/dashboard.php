<?php

function getDashboardTitle()
{
    return _('Dashboard');
}

/**
 * Main function to create the parameters passed to the view.
 *
 * @return array
 */
function get_dashboard()
{
    $shifts = getAllShifts();

    $viewData = array(
        'number_upcoming_shifts' => getNumberUpcomingShifts($shifts, 3*60*60),
        'number_upcoming_night_shifts' => getNumberUpcomingNightShifts(),
        'number_currently_working' => getCurrentlyWorkingAngels(),
        'number_hours_worked' => countHoursToBeWorked($shifts),
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

/**
 * Creates a list of currently running shifts with its subjects as label.
 *
 * @param $shifts
 * @return string
 */
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

/**
 * Creates an ul list of news items with its subjects as label.
 *
 * @return string
 */
function getAllNewsList()
{
    $news = sql_select("SELECT * FROM `News` ORDER BY `Datum`");

    $list = '<ul class="list-group">';
    foreach ($news as $article) {
        $list .= sprintf("<li class='list-group-item'>%s</li> \n", $article['Betreff']);
    }

    return $list.'</ul>';
}

/**
 * @return int
 */
function getCurrentlyWorkingAngels()
{
    $count = count(sql_select("SELECT id FROM `ShiftEntry`;"));

    return $count;
}

/**
 * @param array $shifts
 *
 * @return int
 */
function countHoursToBeWorked($shifts)
{
    $seconds = 0;
    $currentTime = time();

    foreach ($shifts as $shift) {
        if ($shift['start'] >= $currentTime) {
            // has not started yet
            $diff = $shift['end'] - $shift['start'];
            $seconds += $diff > 0 ? $diff : 0;
        } elseif ($shift['end'] >= $currentTime && $shift['start'] <= $currentTime) {
            // shift has started, so just use the time until the end
            $seconds += $shift['end'] - $currentTime;
        }
    }

    return round($seconds/60/60, 0);
}

/**
 * counts the night shifts which are upcoming.
 *
 * @return int
 */
function getNumberUpcomingNightShifts()
{
    $nightShifts = getNightShifts();
    $upcomingNightShifts = array_filter($nightShifts, function ($shift) {
        $currentTime = time();

        return $shift['start'] >= $currentTime || $shift['end'] >= $currentTime;
    });

    return count($upcomingNightShifts);
}

/**
 * @return array
 */
function getNightShifts()
{
    return sql_select("SELECT * FROM Shifts WHERE FROM_UNIXTIME(start, '%H') > 18 OR FROM_UNIXTIME(end, '%H') < 6;");
}
