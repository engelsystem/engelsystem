<?php

/**
 * Strip unwanted characters from a users nick.
 * @param string $nick
 */
function User_validate_Nick($nick) {
  return preg_replace("/([^a-z0-9üöäß. _+*-]{1,})/ui", '', $nick);
}

/**
 * Returns user by id.
 *
 * @param $id UID
 */
function User($id) {
  $user_source = sql_select("SELECT * FROM `User` WHERE `UID`=" . sql_escape($id) . " LIMIT 1");
  if ($user_source === false)
    return false;
  if (count($user_source) > 0)
    return $user_source[0];
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
  if ($user === false)
    return false;
  if (count($user) == 0)
    return null;
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
  if ($user === false)
    return false;
  if (count($user) == 0)
    return null;
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
  if ($user === false)
    return false;
  if (count($user) == 0)
    return null;
  return $user[0];
}

/**
 * Generates a new api key for given user.
 *
 * @param User $user          
 */
function User_reset_api_key(&$user) {
  $user['api_key'] = md5($user['Nick'] . time() . rand());
  $result = sql_query("UPDATE `User` SET `api_key`='" . sql_escape($user['api_key']) . "' WHERE `UID`='" . sql_escape($user['UID']) . "' LIMIT 1");
  if ($result === false)
    return false;
  engelsystem_log("API key resetted.");
}

/**
 * Generates a new password recovery token for given user.
 *
 * @param User $user          
 */
function User_generate_password_recovery_token(&$user) {
  $user['password_recovery_token'] = md5($user['Nick'] . time() . rand());
  $result = sql_query("UPDATE `User` SET `password_recovery_token`='" . sql_escape($user['password_recovery_token']) . "' WHERE `UID`='" . sql_escape($user['UID']) . "' LIMIT 1");
  if ($result === false)
    return false;
  engelsystem_log("Password recovery for " . $user['Nick'] . " started.");
  return $user['password_recovery_token'];
}

?>