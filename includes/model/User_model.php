<?php

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
    db_log_delete('user', $user_id);
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
          `planned_departure_date`=?,
          `updated_microseconds`=?
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
            time_microseconds(),
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
 * @return array
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
 * @return array
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
 * @return array
 */
function User_ids()
{
    return DB::select('SELECT `UID` FROM `User`');
}

/**
 * Strip unwanted characters from a users nick. Allowed are letters, numbers, connecting punctuation and simple space.
 * Nick is trimmed.
 * @param string $nick
 * @return string
 */
function User_validate_Nick($nick)
{
    return preg_replace('/([^\p{L}\p{N}-_ ]+)/ui', '', trim($nick));
}

/**
 * Validate user email address.
 *
 * @param string $mail
 *          The email address to validate
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
 * @param string $jabber
 *          Jabber-ID to validate
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
    if ($planned_arrival_date == null) {
        // null is not okay
        return new ValidationResult(false, time());
    }
    $event_config = EventConfig();
    if ($event_config == null) {
        // Nothing to validate against
        return new ValidationResult(true, $planned_arrival_date);
    }
    if (isset($event_config['buildup_start_date']) && $planned_arrival_date < $event_config['buildup_start_date']) {
        // Planned arrival can not be before buildup start date
        return new ValidationResult(false, $event_config['buildup_start_date']);
    }
    if (isset($event_config['teardown_end_date']) && $planned_arrival_date > $event_config['teardown_end_date']) {
        // Planned arrival can not be after teardown end date
        return new ValidationResult(false, $event_config['teardown_end_date']);
    }
    return new ValidationResult(true, $planned_arrival_date);
}

/**
 * Validate the planned departure date
 *
 * @param int $planned_arrival_date
 *          Unix timestamp
 * @param int $planned_departure_date
 *          Unix timestamp
 * @return ValidationResult
 */
function User_validate_planned_departure_date($planned_arrival_date, $planned_departure_date)
{
    if ($planned_departure_date == null) {
        // null is okay
        return new ValidationResult(true, null);
    }
    if ($planned_arrival_date > $planned_departure_date) {
        // departure cannot be before arrival
        return new ValidationResult(false, $planned_arrival_date);
    }
    $event_config = EventConfig();
    if ($event_config == null) {
        // Nothing to validate against
        return new ValidationResult(true, $planned_departure_date);
    }
    if (isset($event_config['buildup_start_date']) && $planned_departure_date < $event_config['buildup_start_date']) {
        // Planned arrival can not be before buildup start date
        return new ValidationResult(false, $event_config['buildup_start_date']);
    }
    if (isset($event_config['teardown_end_date']) && $planned_departure_date > $event_config['teardown_end_date']) {
        // Planned arrival can not be after teardown end date
        return new ValidationResult(false, $event_config['teardown_end_date']);
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
    return DB::selectOne('SELECT * FROM `User` WHERE `UID`=? LIMIT 1', [$user_id]);
}

/**
 * Returns User by api_key.
 *
 * @param string $api_key
 *          User api key
 * @return array|null Matching user, null if not found
 */
function User_by_api_key($api_key)
{
    return DB::selectOne('SELECT * FROM `User` WHERE `api_key`=? LIMIT 1', [$api_key]);
}

/**
 * Returns User by email.
 *
 * @param string $email
 * @return array|null Matching user, null or false on error
 */
function User_by_email($email)
{
    return DB::selectOne('SELECT * FROM `User` WHERE `email`=? LIMIT 1', [$email]);
}

/**
 * Returns User by password token.
 *
 * @param string $token
 * @return array|null Matching user, null when not found
 */
function User_by_password_recovery_token($token)
{
    return DB::selectOne('SELECT * FROM `User` WHERE `password_recovery_token`=? LIMIT 1', [$token]);
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
    $elegible_vouchers = $shifts_done / $voucher_settings['shifts_per_voucher'] - $earned_vouchers;
    if ($elegible_vouchers < 0) {
        return 0;
    }

    return $elegible_vouchers;
}
