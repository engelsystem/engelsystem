<?php

use Carbon\Carbon;
use Engelsystem\Database\DB;
use Engelsystem\ValidationResult;

/**
 * User model
 */

/**
 * Delete a user
 *
 * @param int $user_id
 */
function User_delete($user_id)
{
    DB::delete('DELETE FROM `User` WHERE `UID`=?', [$user_id]);
}

/**
 * Returns the tshirt score (number of hours counted for tshirt).
 * Accounts only ended shifts.
 *
 * @param array[] $user
 * @return int
 */
function User_tshirt_score($user)
{
    $shift_sum_formula = User_get_shifts_sum_query();
    $result_shifts = DB::selectOne('
        SELECT ROUND((' . $shift_sum_formula . ') / 3600, 2) AS `tshirt_score`
        FROM `User` LEFT JOIN `ShiftEntry` ON `User`.`UID` = `ShiftEntry`.`UID`
        LEFT JOIN `Shifts` ON `ShiftEntry`.`SID` = `Shifts`.`SID` 
        WHERE `User`.`UID` = ?
        AND `Shifts`.`end` < ?
        GROUP BY `User`.`UID`
    ', [
        $user['UID'],
        time()
    ]);
    if (!isset($result_shifts['tshirt_score'])) {
        $result_shifts = ['tshirt_score' => 0];
    }

    $result_worklog = DB::selectOne('
        SELECT SUM(`work_hours`) AS `tshirt_score`
        FROM `User` 
        LEFT JOIN `UserWorkLog` ON `User`.`UID` = `UserWorkLog`.`user_id`
        WHERE `User`.`UID` = ?
        AND `UserWorkLog`.`work_timestamp` < ?
    ', [
        $user['UID'],
        time()
    ]);
    if (!isset($result_worklog['tshirt_score'])) {
        $result_worklog = ['tshirt_score' => 0];
    }

    return $result_shifts['tshirt_score'] + $result_worklog['tshirt_score'];
}

/**
 * Update user.
 *
 * @param array $user
 */
function User_update($user)
{
    DB::update('
          UPDATE `User` SET
          `Nick`=?,
          `Name`=?,
          `Vorname`=?,
          `Alter`=?,
          `Telefon`=?,
          `DECT`=?,
          `Handy`=?,
          `email`=?,
          `email_shiftinfo`=?,
          `email_by_human_allowed`=?,
          `jabber`=?,
          `Size`=?,
          `Gekommen`=?,
          `Aktiv`=?,
          `force_active`=?,
          `Tshirt`=?,
          `color`=?,
          `Sprache`=?,
          `Hometown`=?,
          `got_voucher`=?,
          `arrival_date`=?,
          `planned_arrival_date`=?,
          `planned_departure_date`=?
          WHERE `UID`=?
        ',
        [
            $user['Nick'],
            $user['Name'],
            $user['Vorname'],
            $user['Alter'],
            $user['Telefon'],
            $user['DECT'],
            $user['Handy'],
            $user['email'],
            (int)$user['email_shiftinfo'],
            (int)$user['email_by_human_allowed'],
            $user['jabber'],
            $user['Size'],
            $user['Gekommen'],
            $user['Aktiv'],
            (int)$user['force_active'],
            $user['Tshirt'],
            $user['color'],
            $user['Sprache'],
            $user['Hometown'],
            $user['got_voucher'],
            $user['arrival_date'],
            $user['planned_arrival_date'],
            $user['planned_departure_date'],
            $user['UID'],
        ]
    );
}

/**
 * Counts all forced active users.
 *
 * @return int
 */
function User_force_active_count()
{
    $result = DB::selectOne('SELECT COUNT(*) FROM `User` WHERE `force_active` = 1');

    if (empty($result)) {
        return 0;
    }

    return (int)array_shift($result);
}

/**
 * @return int
 */
function User_active_count()
{
    $result = DB::selectOne('SELECT COUNT(*) FROM `User` WHERE `Aktiv` = 1');

    if (empty($result)) {
        return 0;
    }

    return (int)array_shift($result);
}

/**
 * @return int
 */
function User_got_voucher_count()
{
    $result = DB::selectOne('SELECT SUM(`got_voucher`) FROM `User`');

    if (empty($result)) {
        return 0;
    }

    return (int)array_shift($result);
}

/**
 * @return int
 */
function User_arrived_count()
{
    $result = DB::selectOne('SELECT COUNT(*) FROM `User` WHERE `Gekommen` = 1');

    if (empty($result)) {
        return 0;
    }

    return (int)array_shift($result);
}

/**
 * @return int
 */
function User_tshirts_count()
{
    $result = DB::selectOne('SELECT COUNT(*) FROM `User` WHERE `Tshirt` = 1');

    if (empty($result)) {
        return 0;
    }

    return (int)array_shift($result);
}

/**
 * Returns all column names for sorting in an array.
 *
 * @return array
 */
function User_sortable_columns()
{
    return [
        'Nick',
        'Name',
        'Vorname',
        'Alter',
        'DECT',
        'email',
        'Size',
        'Gekommen',
        'Aktiv',
        'force_active',
        'Tshirt',
        'lastLogIn'
    ];
}

/**
 * Get all users, ordered by Nick by default or by given param.
 *
 * @param string $order_by
 * @return array
 */
function Users($order_by = 'Nick')
{
    return DB::select(sprintf('
            SELECT *
            FROM `User`
            ORDER BY `%s` ASC
        ',
        trim(DB::getPdo()->quote($order_by), '\'')
    ));
}

/**
 * Returns true if user is freeloader
 *
 * @param array $user
 * @return bool
 */
function User_is_freeloader($user)
{
    global $user;

    return count(ShiftEntries_freeloaded_by_user($user)) >= config('max_freeloadable_shifts');
}

/**
 * Returns all users that are not member of given angeltype.
 *
 * @param array $angeltype Angeltype
 * @return array[]
 */
function Users_by_angeltype_inverted($angeltype)
{
    return DB::select('
            SELECT `User`.*
            FROM `User`
            LEFT JOIN `UserAngelTypes`
            ON (`User`.`UID`=`UserAngelTypes`.`user_id` AND `angeltype_id`=?)
            WHERE `UserAngelTypes`.`id` IS NULL
            ORDER BY `Nick`
        ',
        [
            $angeltype['id']
        ]
    );
}

/**
 * Returns all members of given angeltype.
 *
 * @param array $angeltype
 * @return array[]
 */
function Users_by_angeltype($angeltype)
{
    return DB::select('
            SELECT
            `User`.*,
            `UserAngelTypes`.`id` AS `user_angeltype_id`,
            `UserAngelTypes`.`confirm_user_id`,
            `UserAngelTypes`.`supporter`,
            (`UserDriverLicenses`.`user_id` IS NOT NULL) AS `wants_to_drive`,
            `UserDriverLicenses`.*
            FROM `User`
            JOIN `UserAngelTypes` ON `User`.`UID`=`UserAngelTypes`.`user_id`
            LEFT JOIN `UserDriverLicenses` ON `User`.`UID`=`UserDriverLicenses`.`user_id`
            WHERE `UserAngelTypes`.`angeltype_id`=?
            ORDER BY `Nick`
        ',
        [
            $angeltype['id']
        ]
    );
}

/**
 * Returns User id array
 *
 * @return array[]
 */
function User_ids()
{
    return DB::select('SELECT `UID` FROM `User`');
}

/**
 * Strip unwanted characters from a users nick. Allowed are letters, numbers, connecting punctuation and simple space.
 * Nick is trimmed.
 *
 * @param string $nick
 * @return string
 */
function User_validate_Nick($nick)
{
    return preg_replace('/([^\p{L}\p{N}-_. ]+)/ui', '', trim($nick));
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
 * Validate user jabber address
 *
 * @param string $jabber Jabber-ID to validate
 * @return ValidationResult
 */
function User_validate_jabber($jabber)
{
    $jabber = strip_item($jabber);
    if ($jabber == '') {
        // Empty is ok
        return new ValidationResult(true, '');
    }
    return new ValidationResult(check_email($jabber), $jabber);
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
 * Returns user by id.
 *
 * @param int $user_id UID
 * @return array|null
 */
function User($user_id)
{
    $user = DB::selectOne('SELECT * FROM `User` WHERE `UID`=? LIMIT 1', [$user_id]);

    return empty($user) ? null : $user;
}

/**
 * Returns User by api_key.
 *
 * @param string $api_key User api key
 * @return array|null Matching user, null if not found
 */
function User_by_api_key($api_key)
{
    $user = DB::selectOne('SELECT * FROM `User` WHERE `api_key`=? LIMIT 1', [$api_key]);

    return empty($user) ? null : $user;
}

/**
 * Returns User by email.
 *
 * @param string $email
 * @return array|null Matching user, null when not found
 */
function User_by_email($email)
{
    $user = DB::selectOne('SELECT * FROM `User` WHERE `email`=? LIMIT 1', [$email]);

    return empty($user) ? null : $user;
}

/**
 * Returns User by password token.
 *
 * @param string $token
 * @return array|null Matching user, null when not found
 */
function User_by_password_recovery_token($token)
{
    $user = DB::selectOne('SELECT * FROM `User` WHERE `password_recovery_token`=? LIMIT 1', [$token]);

    return empty($user) ? null : $user;
}

/**
 * Generates a new api key for given user.
 *
 * @param array $user
 * @param bool  $log
 */
function User_reset_api_key(&$user, $log = true)
{
    $user['api_key'] = md5($user['Nick'] . time() . rand());
    DB::update('
            UPDATE `User`
            SET `api_key`=?
            WHERE `UID`=?
            LIMIT 1
        ',
        [
            $user['api_key'],
            $user['UID']
        ]
    );

    if ($log) {
        engelsystem_log(sprintf('API key resetted (%s).', User_Nick_render($user)));
    }
}

/**
 * Generates a new password recovery token for given user.
 *
 * @param array $user
 * @return string
 */
function User_generate_password_recovery_token(&$user)
{
    $user['password_recovery_token'] = md5($user['Nick'] . time() . rand());
    DB::update('
            UPDATE `User`
            SET `password_recovery_token`=?
            WHERE `UID`=?
            LIMIT 1
        ',
        [
            $user['password_recovery_token'],
            $user['UID'],
        ]
    );

    engelsystem_log('Password recovery for ' . User_Nick_render($user) . ' started.');

    return $user['password_recovery_token'];
}

/**
 * @param array $user
 * @return float
 */
function User_get_eligable_voucher_count(&$user)
{
    $voucher_settings = config('voucher_settings');
    $shifts_done = count(ShiftEntries_finished_by_user($user));

    $earned_vouchers = $user['got_voucher'] - $voucher_settings['initial_vouchers'];
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
        return 'SUM(`end` - `start`)';
    }

    return sprintf('
            SUM(
                (1 +
                    (
                      (HOUR(FROM_UNIXTIME(`Shifts`.`end`)) > %1$d AND HOUR(FROM_UNIXTIME(`Shifts`.`end`)) < %2$d)
                      OR (HOUR(FROM_UNIXTIME(`Shifts`.`start`)) > %1$d AND HOUR(FROM_UNIXTIME(`Shifts`.`start`)) < %2$d)
                      OR (HOUR(FROM_UNIXTIME(`Shifts`.`start`)) <= %1$d AND HOUR(FROM_UNIXTIME(`Shifts`.`end`)) >= %2$d)
                    )
                )
                * (`Shifts`.`end` - `Shifts`.`start`)
                * (1 - (%3$d + 1) * `ShiftEntry`.`freeloaded`)
            )
        ',
        $nightShifts['start'],
        $nightShifts['end'],
        $nightShifts['multiplier']
    );
}
