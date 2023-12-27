<?php

use Carbon\Carbon;
use Engelsystem\Database\Db;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

/**
 * User model
 */

/**
 * Returns the tshirt score (number of hours counted for tshirt).
 * Accounts only ended shifts.
 *
 * @param int $userId
 * @return int
 */
function User_tshirt_score($userId)
{
    $shift_sum_formula = User_get_shifts_sum_query();
    $result_shifts = Db::selectOne(sprintf('
        SELECT ROUND((%s) / 3600, 2) AS `tshirt_score`
        FROM `users` LEFT JOIN `shift_entries` ON `users`.`id` = `shift_entries`.`user_id`
        LEFT JOIN `shifts` ON `shift_entries`.`shift_id` = `shifts`.`id`
        WHERE `users`.`id` = ?
        AND `shifts`.`end` < NOW()
        GROUP BY `users`.`id`
    ', $shift_sum_formula), [
        $userId,
    ]);
    if (!isset($result_shifts['tshirt_score'])) {
        $result_shifts = ['tshirt_score' => 0];
    }

    $worklogHours = Worklog::query()
        ->where('user_id', $userId)
        ->where('worked_at', '<=', Carbon::Now())
        ->sum('hours');

    return $result_shifts['tshirt_score'] + $worklogHours;
}

/**
 * Returns all users that are not member of given angeltype.
 *
 * @param AngelType $angeltype Angeltype
 *
 * @return User[]|Collection
 */
function Users_by_angeltype_inverted(AngelType $angeltype)
{
    return User::query()
        ->select('users.*')
        ->leftJoin('user_angel_type', function ($query) use ($angeltype) {
            /** @var JoinClause $query */
            $query
                ->on('users.id', '=', 'user_angel_type.user_id')
                ->where('user_angel_type.angel_type_id', '=', $angeltype->id);
        })
        ->whereNull('user_angel_type.id')
        ->orderBy('users.name')
        ->get();
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

    return sprintf(
        '
            COALESCE(SUM(
                (1 + (
                    (HOUR(shifts.end) > %1$d AND HOUR(shifts.end) < %2$d)
                    OR (HOUR(shifts.start) > %1$d AND HOUR(shifts.start) < %2$d)
                    OR (HOUR(shifts.start) <= %1$d AND HOUR(shifts.end) >= %2$d)
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
