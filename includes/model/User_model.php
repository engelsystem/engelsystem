<?php

use Carbon\Carbon;
use Engelsystem\Database\Db;
use Engelsystem\Models\User\User;
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
 * @param User $user
 * @return float
 */
function User_get_eligable_voucher_count($user)
{
    $voucher_settings = config('voucher_settings');
    $start = $voucher_settings['voucher_start']
        ? Carbon::createFromFormat('Y-m-d', $voucher_settings['voucher_start'])->setTime(0, 0)
        : null;

    $shiftEntries = ShiftEntries_finished_by_user($user, $start);
    $worklog = $user->worklogs()
        ->whereDate('worked_at', '>=', $start ?: 0)
        ->with(['user', 'creator'])
        ->get();
    $shifts_done =
        count($shiftEntries)
        + $worklog->count();

    $shiftsTime = 0;
    foreach ($shiftEntries as $shiftEntry) {
        $shiftsTime += ($shiftEntry->shift->end->timestamp - $shiftEntry->shift->start->timestamp) / 60 / 60;
    }
    foreach ($worklog as $entry) {
        $shiftsTime += $entry->hours;
    }

    $vouchers = $voucher_settings['initial_vouchers'];
    if ($voucher_settings['shifts_per_voucher']) {
        $vouchers += $shifts_done / $voucher_settings['shifts_per_voucher'];
    }
    if ($voucher_settings['hours_per_voucher']) {
        $vouchers += $shiftsTime / $voucher_settings['hours_per_voucher'];
    }

    $vouchers -= $user->state->got_voucher;
    $vouchers = floor($vouchers);
    if ($vouchers < 0) {
        return 0;
    }

    return $vouchers;
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
                * (1 - (%3$d + 1) * `shift_entries`.`freeloaded`)
            ), 0)
        ',
        $nightShifts['start'],
        $nightShifts['end'],
        $nightShifts['multiplier']
    );
}
