<?php

use Carbon\Carbon;
use Engelsystem\Database\Db;
use Engelsystem\Models\Worklog;

/**
 * User model
 */

/**
 * Returns the goodie score (number of hours counted for tshirt).
 * Accounts only ended shifts.
 *
 * @param int $userId
 * @return float
 */
function User_goodie_score(int $userId): float
{
    $shift_sum_formula = User_get_shifts_sum_query();
    $result_shifts = Db::selectOne(sprintf('
        SELECT ROUND((%s) / 3600, 2) AS `goodie_score`
        FROM `users` LEFT JOIN `shift_entries` ON `users`.`id` = `shift_entries`.`user_id`
        LEFT JOIN `shifts` ON `shift_entries`.`shift_id` = `shifts`.`id`
        WHERE `users`.`id` = ?
        AND `shifts`.`end` < NOW()
        GROUP BY `users`.`id`
    ', $shift_sum_formula), [
        $userId,
    ]);
    if (!isset($result_shifts['goodie_score'])) {
        $result_shifts = ['goodie_score' => 0];
    }

    $worklogHours = Worklog::query()
        ->where('user_id', $userId)
        ->where('worked_at', '<=', Carbon::Now())
        ->sum('hours');

    return $result_shifts['goodie_score'] + $worklogHours;
}

/**
 * Generates the query to sum night shifts
 *
 * @return string
 */
function User_get_shifts_sum_query()
{
    $nightShifts = config('night_shifts');
    if (!$nightShifts['enabled']) {
        return 'COALESCE(SUM(UNIX_TIMESTAMP(shifts.end) - UNIX_TIMESTAMP(shifts.start)), 0)';
    }

    /* @see \Engelsystem\Models\Shifts\Shift::isNightShift to keep it in sync */
    return sprintf(
        '
            COALESCE(SUM(
                (1 + (
                    /* Starts during night */
                    HOUR(shifts.start) >= %1$d AND HOUR(shifts.start) < %2$d
                    /* Ends during night */
                    OR (
                        HOUR(shifts.end) > %1$d
                        || HOUR(shifts.end) = %1$d AND MINUTE(shifts.end) > 0
                    ) AND HOUR(shifts.end) <= %2$d
                    /* Starts before and ends after night */
                    OR HOUR(shifts.start) <= %1$d AND HOUR(shifts.end) >= %2$d
                ))
                * (UNIX_TIMESTAMP(shifts.end) - UNIX_TIMESTAMP(shifts.start))
                * (1 - (%3$d + 1)
                * (CASE
                    WHEN `shift_entries`.`freeloaded_by` IS NULL THEN 0
                    ELSE 1
                    END))
            ), 0)
        ',
        $nightShifts['start'],
        $nightShifts['end'],
        $nightShifts['multiplier']
    );
}
