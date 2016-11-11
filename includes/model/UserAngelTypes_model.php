<?php

/**
 * User angeltypes model
 */

/**
 * Checks if a user joined an angeltype.
 *
 * @param User $user
 *          The user to be checked
 * @param Angeltype $angeltype
 *          The angeltype to be checked
 * @return boolean
 */
function UserAngelType_exists($user, $angeltype) {
  return sql_num_query("
      SELECT `id` 
      FROM `UserAngelTypes`
      WHERE `UserAngelTypes`.`user_id`='" . sql_escape($user['UID']) . "'
      AND `angeltype_id`='" . sql_escape($angeltype['id']) . "'
      ") > 0;
}

/**
 * List users angeltypes.
 *
 * @param User $user          
 */
function User_angeltypes($user) {
  $result = sql_select("
      SELECT `AngelTypes`.*, `UserAngelTypes`.`confirm_user_id`, `UserAngelTypes`.`supporter`
      FROM `UserAngelTypes`
      JOIN `AngelTypes` ON `UserAngelTypes`.`angeltype_id` = `AngelTypes`.`id`
      WHERE `UserAngelTypes`.`user_id`='" . sql_escape($user['UID']) . "'
      ");
  if ($result === false) {
    engelsystem_error("Unable to load user angeltypes.");
    return false;
  }
  return $result;
}

/**
 * Gets unconfirmed user angeltypes for angeltypes of which the given user is a supporter.
 *
 * @param User $user          
 */
function User_unconfirmed_AngelTypes($user) {
  $result = sql_select("
    SELECT 
      `UserAngelTypes`.*, 
      `AngelTypes`.`name`, 
      count(`UnconfirmedMembers`.`user_id`) as `count` 
    FROM `UserAngelTypes`
    JOIN `AngelTypes` ON `UserAngelTypes`.`angeltype_id`=`AngelTypes`.`id`
    JOIN `UserAngelTypes` as `UnconfirmedMembers` ON `UserAngelTypes`.`angeltype_id`=`UnconfirmedMembers`.`angeltype_id`
    WHERE `UserAngelTypes`.`user_id`='" . sql_escape($user['UID']) . "'
      AND `UserAngelTypes`.`supporter`=TRUE
      AND `AngelTypes`.`restricted`=TRUE
      AND `UnconfirmedMembers`.`confirm_user_id` IS NULL
    GROUP BY `UserAngelTypes`.`angeltype_id`
    ORDER BY `AngelTypes`.`name`");
  if ($result === false) {
    engelsystem_error("Unable to load user angeltypes.");
  }
  return $result;
}

/**
 * Returns true if user is angeltype supporter or has privilege admin_user_angeltypes.
 *
 * @param User $user          
 * @param AngelType $angeltype          
 */
function User_is_AngelType_supporter($user, $angeltype) {
  return (sql_num_query("
      SELECT `id` 
      FROM `UserAngelTypes` 
      WHERE `user_id`='" . sql_escape($user['UID']) . "'
      AND `angeltype_id`='" . sql_escape($angeltype['id']) . "'
      AND `supporter`=TRUE
      LIMIT 1") > 0) || in_array('admin_user_angeltypes', privileges_for_user($user['UID']));
}

/**
 * Add or remove supporter rights.
 *
 * @param int $user_angeltype_id          
 * @param bool $supporter          
 */
function UserAngelType_update($user_angeltype_id, $supporter) {
  $result = sql_query("
      UPDATE `UserAngelTypes`
      SET `supporter`=" . sql_bool($supporter) . "
      WHERE `id`='" . sql_escape($user_angeltype_id) . "'
      LIMIT 1");
  if ($result === false) {
    engelsystem_error("Unable to update supporter rights.");
  }
  return $result;
}

/**
 * Delete all unconfirmed UserAngelTypes for given Angeltype.
 *
 * @param int $angeltype_id          
 */
function UserAngelTypes_delete_all($angeltype_id) {
  $result = sql_query("
      DELETE FROM `UserAngelTypes`
      WHERE `angeltype_id`='" . sql_escape($angeltype_id) . "'
      AND `confirm_user_id` IS NULL");
  if ($result === false) {
    engelsystem_error("Unable to delete all unconfirmed users.");
  }
  return $result;
}

/**
 * Confirm all unconfirmed UserAngelTypes for given Angeltype.
 *
 * @param int $angeltype_id          
 * @param User $confirm_user          
 */
function UserAngelTypes_confirm_all($angeltype_id, $confirm_user) {
  $result = sql_query("
      UPDATE `UserAngelTypes`
      SET `confirm_user_id`='" . sql_escape($confirm_user['UID']) . "'
      WHERE `angeltype_id`='" . sql_escape($angeltype_id) . "'
      AND `confirm_user_id` IS NULL");
  if ($result === false) {
    engelsystem_error("Unable to confirm all users.");
  }
  return $result;
}

/**
 * Confirm an UserAngelType with confirming user.
 *
 * @param int $user_angeltype_id          
 * @param User $confirm_user          
 */
function UserAngelType_confirm($user_angeltype_id, $confirm_user) {
  $result = sql_query("
      UPDATE `UserAngelTypes`
      SET `confirm_user_id`='" . sql_escape($confirm_user['UID']) . "'
      WHERE `id`='" . sql_escape($user_angeltype_id) . "'
      LIMIT 1");
  if ($result === false) {
    engelsystem_error("Unable to confirm user angeltype.");
  }
  return $result;
}

/**
 * Delete an UserAngelType.
 *
 * @param UserAngelType $user_angeltype          
 */
function UserAngelType_delete($user_angeltype) {
  return sql_query("
      DELETE FROM `UserAngelTypes` 
      WHERE `id`='" . sql_escape($user_angeltype['id']) . "' 
      LIMIT 1");
}

/**
 * Create an UserAngelType.
 *
 * @param User $user          
 * @param Angeltype $angeltype          
 */
function UserAngelType_create($user, $angeltype) {
  $result = sql_query("
    INSERT INTO `UserAngelTypes` SET
    `user_id`='" . sql_escape($user['UID']) . "',
    `angeltype_id`='" . sql_escape($angeltype['id']) . "'");
  if ($result === false) {
    engelsystem_error("Unable to create user angeltype.");
  }
  return sql_id();
}

/**
 * Get an UserAngelType by its id.
 *
 * @param int $user_angeltype_id          
 */
function UserAngelType($user_angeltype_id) {
  $angeltype = sql_select("
      SELECT *
      FROM `UserAngelTypes`
      WHERE `id`='" . sql_escape($user_angeltype_id) . "'
      LIMIT 1");
  if ($angeltype === false) {
    engelsystem_error("Unable to load user angeltype.");
  }
  if (count($angeltype) == 0) {
    return null;
  }
  return $angeltype[0];
}

/**
 * Get an UserAngelType by user and angeltype.
 *
 * @param User $user          
 * @param Angeltype $angeltype          
 */
function UserAngelType_by_User_and_AngelType($user, $angeltype) {
  $angeltype = sql_select("
      SELECT * 
      FROM `UserAngelTypes` 
      WHERE `user_id`='" . sql_escape($user['UID']) . "'
      AND `angeltype_id`='" . sql_escape($angeltype['id']) . "'
      LIMIT 1");
  if ($angeltype === false) {
    engelsystem_error("Unable to load user angeltype.");
  }
  if (count($angeltype) == 0) {
    return null;
  }
  return $angeltype[0];
}
?>