<?php

use Carbon\Carbon;
use Engelsystem\Database\DB;
use Engelsystem\Models\User\PasswordReset;
use Engelsystem\Models\User\User;
use Engelsystem\ValidationResult;
use Illuminate\Database\Query\JoinClause;

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
    $result_shifts = DB::selectOne(sprintf('
        SELECT ROUND((%s) / 3600, 2) AS `tshirt_score`
        FROM `users` LEFT JOIN `ShiftEntry` ON `users`.`id` = `ShiftEntry`.`UID`
        LEFT JOIN `Shifts` ON `ShiftEntry`.`SID` = `Shifts`.`SID` 
        WHERE `users`.`id` = ?
        AND `Shifts`.`end` < ?
        GROUP BY `users`.`id`
    ', $shift_sum_formula), [
        $userId,
        time()
    ]);
    if (!isset($result_shifts['tshirt_score'])) {
        $result_shifts = ['tshirt_score' => 0];
    }

    $result_worklog = DB::selectOne('
        SELECT SUM(`work_hours`) AS `tshirt_score`
        FROM `users` 
        LEFT JOIN `UserWorkLog` ON `users`.`id` = `UserWorkLog`.`user_id`
        WHERE `users`.`id` = ?
        AND `UserWorkLog`.`work_timestamp` < ?
    ', [
        $userId,
        time()
    ]);
    if (!isset($result_worklog['tshirt_score'])) {
        $result_worklog = ['tshirt_score' => 0];
    }

    return $result_shifts['tshirt_score'] + $result_worklog['tshirt_score'];
}

/**
 * Returns true if user is freeloader
 *
 * @param User $user
 * @return bool
 */
function User_is_freeloader($user)
{
    return count(ShiftEntries_freeloaded_by_user($user->id)) >= config('max_freeloadable_shifts');
}

/**
 * Returns all users that are not member of given angeltype.
 *
 * @param array $angeltype Angeltype
 * @return User[]
 */
function Users_by_angeltype_inverted($angeltype)
{
    return User::query()
        ->select('users.*')
        ->leftJoin('UserAngelTypes', function ($query) use ($angeltype) {
            /** @var JoinClause $query */
            $query
                ->on('users.id', '=', 'UserAngelTypes.user_id')
                ->where('UserAngelTypes.angeltype_id', '=', $angeltype['id']);
        })
        ->whereNull('UserAngelTypes.id')
        ->orderBy('users.name')
        ->get();
}

/**
 * Returns all members of given angeltype.
 *
 * @param array $angeltype
 * @return User[]
 */
function Users_by_angeltype($angeltype)
{
    return User::query()
        ->select('users.*',
            'UserAngelTypes.id AS user_angeltype_id',
            'UserAngelTypes.confirm_user_id',
            'UserAngelTypes.supporter',
            'UserDriverLicenses.user_id AS wants_to_drive',
            'UserDriverLicenses.*'
        )
        ->join('UserAngelTypes', 'users.id', '=', 'UserAngelTypes.user_id')
        ->leftJoin('UserDriverLicenses', 'users.id', '=', 'UserDriverLicenses.user_id')
        ->where('UserAngelTypes.angeltype_id', '=', $angeltype['id'])
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

    if (strlen($nick) == 0 || strlen($nick) > 23) {
        return new ValidationResult(false, $nick);
    }
    if (preg_match('/([^\p{L}\p{N}\-_. ]+)/ui', $nick)) {
        return new ValidationResult(false, $nick);
    }

    return new ValidationResult(true, $nick);
}

/**
 * Validate user email address.
 *
 * @param string $mail The email address to validate
 * @return ValidationResult
 */
function User_validate_mail($mail)
{
    $mail = strip_item($mail);
    return new ValidationResult(check_email($mail), $mail);
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
    if (!empty($buildup) && $buildup->greaterThan(Carbon::createFromTimestamp($planned_arrival_date))) {
        // Planned arrival can not be before buildup start date
        return new ValidationResult(false, $buildup->getTimestamp());
    }

    /** @var Carbon $teardown */
    if (!empty($teardown) && $teardown->lessThan(Carbon::createFromTimestamp($planned_arrival_date))) {
        // Planned arrival can not be after teardown end date
        return new ValidationResult(false, $teardown->getTimestamp());
    }

    return new ValidationResult(true, $planned_arrival_date);
}

/**
 * Validate the planned departure date
 *
 * @param int $planned_arrival_date   Unix timestamp
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
    if (!empty($buildup) && $buildup->greaterThan(Carbon::createFromTimestamp($planned_departure_date))) {
        // Planned arrival can not be before buildup start date
        return new ValidationResult(false, $buildup->getTimestamp());
    }

    /** @var Carbon $teardown */
    if (!empty($teardown) && $teardown->lessThan(Carbon::createFromTimestamp($planned_departure_date))) {
        // Planned arrival can not be after teardown end date
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
    $user->api_key = md5($user->name . time() . rand());
    $user->save();

    if ($log) {
        engelsystem_log(sprintf('API key resetted (%s).', User_Nick_render($user, true)));
    }
}

/**
 * Generates a new password recovery token for given user.
 *
 * @param User $user
 * @return string
 */
function User_generate_password_recovery_token($user)
{
    $reset = PasswordReset::findOrNew($user->id);
    $reset->user_id = $user->id;
    $reset->token = md5($user->name . time() . rand());
    $reset->save();

    engelsystem_log('Password recovery for ' . User_Nick_render($user, true) . ' started.');

    return $reset->token;
}

/**
 * @param User $user
 * @return float
 */
function User_get_eligable_voucher_count($user)
{
    $voucher_settings = config('voucher_settings');
    $shifts_done = count(ShiftEntries_finished_by_user($user->id));

    $earned_vouchers = $user->state->got_voucher - $voucher_settings['initial_vouchers'];
    $eligable_vouchers = $shifts_done / $voucher_settings['shifts_per_voucher'] - $earned_vouchers;
    if ($eligable_vouchers < 0) {
        return 0;
    }

    return $eligable_vouchers;
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
        return 'COALESCE(SUM(`end` - `start`), 0)';
    }

    return sprintf('
            COALESCE(SUM(
                (1 +
                    (
                      (HOUR(FROM_UNIXTIME(`Shifts`.`end`)) > %1$d AND HOUR(FROM_UNIXTIME(`Shifts`.`end`)) < %2$d)
                      OR (HOUR(FROM_UNIXTIME(`Shifts`.`start`)) > %1$d AND HOUR(FROM_UNIXTIME(`Shifts`.`start`)) < %2$d)
                      OR (HOUR(FROM_UNIXTIME(`Shifts`.`start`)) <= %1$d AND HOUR(FROM_UNIXTIME(`Shifts`.`end`)) >= %2$d)
                    )
                )
                * (`Shifts`.`end` - `Shifts`.`start`)
                * (1 - (%3$d + 1) * `ShiftEntry`.`freeloaded`)
            ), 0)
        ',
        $nightShifts['start'],
        $nightShifts['end'],
        $nightShifts['multiplier']
    );
}
