<?php

use Carbon\Carbon;
use Engelsystem\Database\Db;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;
use Engelsystem\ValidationResult;
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
 * Strip unwanted characters from a users nick. Allowed are letters, numbers, connecting punctuation and simple space.
 * Nick is trimmed.
 *
 * @param string $nick
 * @return ValidationResult
 */
function User_validate_Nick($nick)
{
    $nick = trim($nick);

    if (strlen($nick) == 0 || strlen($nick) > 24) {
        return new ValidationResult(false, $nick);
    }
    if (preg_match(config('username_regex', '/([^\p{L}\p{N}\-_. ]+)/ui'), $nick)) {
        return new ValidationResult(false, $nick);
    }

    return new ValidationResult(true, $nick);
}

/**
 * Validate the planned arrival date
 *
 * @param int $planned_arrival_date Unix timestamp
 * @return ValidationResult
 */
function User_validate_planned_arrival_date($planned_arrival_date)
{
    if (is_null($planned_arrival_date)) {
        // null is not okay
        return new ValidationResult(false, time());
    }

    $config = config();
    $buildup = $config->get('buildup_start');
    $teardown = $config->get('teardown_end');

    /** @var Carbon $buildup */
    if (!empty($buildup) && Carbon::createFromTimestamp($planned_arrival_date)->lessThan($buildup->setTime(0, 0))) {
        // Planned arrival can not be before buildup start date
        return new ValidationResult(false, $buildup->getTimestamp());
    }

    /** @var Carbon $teardown */
    if (!empty($teardown) && Carbon::createFromTimestamp($planned_arrival_date)->greaterThanOrEqualTo($teardown->addDay()->setTime(0, 0))) {
        // Planned arrival can not be after teardown end date
        return new ValidationResult(false, $teardown->getTimestamp());
    }

    return new ValidationResult(true, $planned_arrival_date);
}

/**
 * Validate the planned departure date
 *
 * @param int $planned_arrival_date Unix timestamp
 * @param int $planned_departure_date Unix timestamp
 * @return ValidationResult
 */
function User_validate_planned_departure_date($planned_arrival_date, $planned_departure_date)
{
    if (is_null($planned_departure_date)) {
        // null is okay
        return new ValidationResult(true, null);
    }

    if ($planned_arrival_date > $planned_departure_date) {
        // departure cannot be before arrival
        return new ValidationResult(false, $planned_arrival_date);
    }

    $config = config();
    $buildup = $config->get('buildup_start');
    $teardown = $config->get('teardown_end');

    /** @var Carbon $buildup */
    if (!empty($buildup) && Carbon::createFromTimestamp($planned_departure_date)->lessThan($buildup->setTime(0, 0))) {
        // Planned departure can not be before buildup start date
        return new ValidationResult(false, $buildup->getTimestamp());
    }

    /** @var Carbon $teardown */
    if (!empty($teardown) && Carbon::createFromTimestamp($planned_departure_date)->greaterThanOrEqualTo($teardown->addDay()->setTime(0, 0))) {
        // Planned departure can not be after teardown end date
        return new ValidationResult(false, $teardown->getTimestamp());
    }

    return new ValidationResult(true, $planned_departure_date);
}

/**
 * Generates a new api key for given user.
 *
 * @param User $user
 * @param bool $log
 */
function User_reset_api_key($user, $log = true)
{
    $user->api_key = bin2hex(random_bytes(32));
    $user->save();

    if ($log) {
        engelsystem_log(sprintf('API key resetted (%s).', User_Nick_render($user, true)));
    }
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
    $worklog = UserWorkLogsForUser($user->id, $start);
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
