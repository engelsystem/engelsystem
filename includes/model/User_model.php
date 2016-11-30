<?php
use Engelsystem\ValidationResult;

/**
 * User model
 */

/**
 * Delete a user
 *
 * @param int $user_id          
 */
function User_delete($user_id) {
  return sql_query("DELETE FROM `User` WHERE `UID`='" . sql_escape($user_id) . "'");
}

/**
 * Update user.
 *
 * @param User $user          
 */
function User_update($user) {
  return sql_query("UPDATE `User` SET
      `Nick`='" . sql_escape($user['Nick']) . "',
      `Name`='" . sql_escape($user['Name']) . "',
      `Vorname`='" . sql_escape($user['Vorname']) . "',
      `Alter`='" . sql_escape($user['Alter']) . "',
      `Telefon`='" . sql_escape($user['Telefon']) . "',
      `DECT`='" . sql_escape($user['DECT']) . "',
      `Handy`='" . sql_escape($user['Handy']) . "',
      `email`='" . sql_escape($user['email']) . "',
      `email_shiftinfo`=" . sql_bool($user['email_shiftinfo']) . ",
      `email_by_human_allowed`=" . sql_bool($user['email_by_human_allowed']) . ",
      `jabber`='" . sql_escape($user['jabber']) . "',
      `Size`='" . sql_escape($user['Size']) . "',
      `Gekommen`='" . sql_escape($user['Gekommen']) . "',
      `Aktiv`='" . sql_escape($user['Aktiv']) . "',
      `force_active`=" . sql_bool($user['force_active']) . ",
      `Tshirt`='" . sql_escape($user['Tshirt']) . "',
      `color`='" . sql_escape($user['color']) . "',
      `Sprache`='" . sql_escape($user['Sprache']) . "',
      `Hometown`='" . sql_escape($user['Hometown']) . "',
      `got_voucher`='" . sql_escape($user['got_voucher']) . "',
      `arrival_date`='" . sql_escape($user['arrival_date']) . "',
      `planned_arrival_date`='" . sql_escape($user['planned_arrival_date']) . "',
      `planned_departure_date`=" . sql_null($user['planned_departure_date']) . "
      WHERE `UID`='" . sql_escape($user['UID']) . "'");
}

/**
 * Counts all forced active users.
 */
function User_force_active_count() {
  return sql_select_single_cell("SELECT COUNT(*) FROM `User` WHERE `force_active` = 1");
}

function User_active_count() {
  return sql_select_single_cell("SELECT COUNT(*) FROM `User` WHERE `Aktiv` = 1");
}

function User_got_voucher_count() {
  return sql_select_single_cell("SELECT SUM(`got_voucher`) FROM `User`");
}

function User_arrived_count() {
  return sql_select_single_cell("SELECT COUNT(*) FROM `User` WHERE `Gekommen` = 1");
}

function User_tshirts_count() {
  return sql_select_single_cell("SELECT COUNT(*) FROM `User` WHERE `Tshirt` = 1");
}

/**
 * Returns all column names for sorting in an array.
 */
function User_sortable_columns() {
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
 */
function Users($order_by = 'Nick') {
  return sql_select("SELECT * FROM `User` ORDER BY `" . sql_escape($order_by) . "` ASC");
}

/**
 * Returns true if user is freeloader
 *
 * @param User $user          
 */
function User_is_freeloader($user) {
  global $max_freeloadable_shifts, $user;
  
  return count(ShiftEntries_freeloaded_by_user($user)) >= $max_freeloadable_shifts;
}

/**
 * Returns all users that are not member of given angeltype.
 *
 * @param Angeltype $angeltype          
 */
function Users_by_angeltype_inverted($angeltype) {
  $result = sql_select("
      SELECT `User`.*
      FROM `User`
      LEFT JOIN `UserAngelTypes` ON (`User`.`UID`=`UserAngelTypes`.`user_id` AND `angeltype_id`='" . sql_escape($angeltype['id']) . "')
      WHERE `UserAngelTypes`.`id` IS NULL
      ORDER BY `Nick`");
  if ($result === false) {
    engelsystem_error("Unable to load users.");
  }
  return $result;
}

/**
 * Returns all members of given angeltype.
 *
 * @param Angeltype $angeltype          
 */
function Users_by_angeltype($angeltype) {
  $result = sql_select("
      SELECT
      `User`.*,
      `UserAngelTypes`.`id` as `user_angeltype_id`,
      `UserAngelTypes`.`confirm_user_id`,
      `UserAngelTypes`.`supporter`,
      `UserDriverLicenses`.*
      FROM `User`
      JOIN `UserAngelTypes` ON `User`.`UID`=`UserAngelTypes`.`user_id`
      LEFT JOIN `UserDriverLicenses` ON `User`.`UID`=`UserDriverLicenses`.`user_id`
      WHERE `UserAngelTypes`.`angeltype_id`='" . sql_escape($angeltype['id']) . "'
      ORDER BY `Nick`");
  if ($result === false) {
    engelsystem_error("Unable to load members.");
  }
  return $result;
}

/**
 * Returns User id array
 */
function User_ids() {
  return sql_select("SELECT `UID` FROM `User`");
}

/**
 * Strip unwanted characters from a users nick.
 *
 * @param string $nick          
 */
function User_validate_Nick($nick) {
  return preg_replace("/([^a-z0-9üöäß. _+*-]{1,})/ui", '', $nick);
}

/**
 * Validate user email address.
 *
 * @param string $mail
 *          The email address to validate
 * @return ValidationResult
 */
function User_validate_mail($mail) {
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
function User_validate_jabber($jabber) {
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
 * @param int $planned_arrival_date
 *          Unix timestamp
 * @return ValidationResult
 */
function User_validate_planned_arrival_date($planned_arrival_date) {
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
function User_validate_planned_departure_date($planned_arrival_date, $planned_departure_date) {
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
 * @param $user_id UID          
 */
function User($user_id) {
  $user_source = sql_select("SELECT * FROM `User` WHERE `UID`='" . sql_escape($user_id) . "' LIMIT 1");
  if ($user_source === false) {
    engelsystem_error("Unable to load user.");
  }
  if (count($user_source) > 0) {
    return $user_source[0];
  }
  return null;
}

/**
 * Returns User by api_key.
 *
 * @param string $api_key
 *          User api key
 * @return Matching user, null or false on error
 */
function User_by_api_key($api_key) {
  $user = sql_select("SELECT * FROM `User` WHERE `api_key`='" . sql_escape($api_key) . "' LIMIT 1");
  if ($user === false) {
    engelsystem_error("Unable to find user by api key.");
  }
  if (count($user) == 0) {
    return null;
  }
  return $user[0];
}

/**
 * Returns User by email.
 *
 * @param string $email          
 * @return Matching user, null or false on error
 */
function User_by_email($email) {
  $user = sql_select("SELECT * FROM `User` WHERE `email`='" . sql_escape($email) . "' LIMIT 1");
  if ($user === false) {
    engelsystem_error("Unable to load user.");
  }
  if (count($user) == 0) {
    return null;
  }
  return $user[0];
}

/**
 * Returns User by password token.
 *
 * @param string $token          
 * @return Matching user, null or false on error
 */
function User_by_password_recovery_token($token) {
  $user = sql_select("SELECT * FROM `User` WHERE `password_recovery_token`='" . sql_escape($token) . "' LIMIT 1");
  if ($user === false) {
    engelsystem_error("Unable to load user.");
  }
  if (count($user) == 0) {
    return null;
  }
  return $user[0];
}

/**
 * Generates a new api key for given user.
 *
 * @param User $user          
 */
function User_reset_api_key(&$user, $log = true) {
  $user['api_key'] = md5($user['Nick'] . time() . rand());
  $result = sql_query("UPDATE `User` SET `api_key`='" . sql_escape($user['api_key']) . "' WHERE `UID`='" . sql_escape($user['UID']) . "' LIMIT 1");
  if ($result === false) {
    return false;
  }
  if ($log) {
    engelsystem_log(sprintf("API key resetted (%s).", User_Nick_render($user)));
  }
}

/**
 * Generates a new password recovery token for given user.
 *
 * @param User $user          
 */
function User_generate_password_recovery_token(&$user) {
  $user['password_recovery_token'] = md5($user['Nick'] . time() . rand());
  $result = sql_query("UPDATE `User` SET `password_recovery_token`='" . sql_escape($user['password_recovery_token']) . "' WHERE `UID`='" . sql_escape($user['UID']) . "' LIMIT 1");
  if ($result === false) {
    engelsystem_error("Unable to generate password recovery token.");
  }
  engelsystem_log("Password recovery for " . User_Nick_render($user) . " started.");
  return $user['password_recovery_token'];
}

function User_get_eligable_voucher_count(&$user) {
  global $voucher_settings;
  
  $shifts_done = count(ShiftEntries_finished_by_user($user));
  
  $earned_vouchers = $user['got_voucher'] - $voucher_settings['initial_vouchers'];
  $elegible_vouchers = $shifts_done / $voucher_settings['shifts_per_voucher'] - $earned_vouchers;
  if ($elegible_vouchers < 0) {
    return 0;
  }
  
  return $elegible_vouchers;
}

?>
