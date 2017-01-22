<?php

use Engelsystem\Database\DB;

/**
 * User angeltypes model
 */

/**
 * Checks if a user joined an angeltype.
 *
 * @param array $user      The user to be checked
 * @param array $angeltype The angeltype to be checked
 * @return boolean
 */
function UserAngelType_exists($user, $angeltype)
{
    return count(DB::select('
      SELECT `id`
      FROM `UserAngelTypes`
      WHERE `UserAngelTypes`.`user_id`=?
      AND `angeltype_id`=?
      ', [$user['UID'], $angeltype['id']])) > 0;
}

/**
 * List users angeltypes.
 *
 * @param array $user
 * @return array|false
 */
function User_angeltypes($user)
{
    $result = DB::select('
      SELECT `AngelTypes`.*, `UserAngelTypes`.`confirm_user_id`, `UserAngelTypes`.`supporter`
      FROM `UserAngelTypes`
      JOIN `AngelTypes` ON `UserAngelTypes`.`angeltype_id` = `AngelTypes`.`id`
      WHERE `UserAngelTypes`.`user_id`=?
      ', [$user['UID']]);

    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to load user angeltypes.');
        return false;
    }

    return $result;
}

/**
 * Gets unconfirmed user angeltypes for angeltypes of which the given user is a supporter.
 *
 * @param array $user
 * @return array
 */
function User_unconfirmed_AngelTypes($user)
{
    $result = DB::select('
        SELECT
          `UserAngelTypes`.*,
          `AngelTypes`.`name`,
          count(`UnconfirmedMembers`.`user_id`) AS `count`
        FROM `UserAngelTypes`
        JOIN `AngelTypes` ON `UserAngelTypes`.`angeltype_id`=`AngelTypes`.`id`
        JOIN `UserAngelTypes` AS `UnconfirmedMembers` ON `UserAngelTypes`.`angeltype_id`=`UnconfirmedMembers`.`angeltype_id`
        WHERE `UserAngelTypes`.`user_id`=?
          AND `UserAngelTypes`.`supporter`=TRUE
          AND `AngelTypes`.`restricted`=TRUE
          AND `UnconfirmedMembers`.`confirm_user_id` IS NULL
        GROUP BY `UserAngelTypes`.`angeltype_id`
        ORDER BY `AngelTypes`.`name`
    ', [$user['UID']]);

    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to load user angeltypes.');
    }

    return $result;
}

/**
 * Returns true if user is angeltype supporter or has privilege admin_user_angeltypes.
 *
 * @param array $user
 * @param array $angeltype
 * @return bool
 */
function User_is_AngelType_supporter(&$user, $angeltype)
{
    if (!isset($user['privileges'])) {
        $user['privileges'] = privileges_for_user($user['UID']);
    }
    return (count(DB::select('
                      SELECT `id`
                      FROM `UserAngelTypes`
                      WHERE `user_id`=?
                      AND `angeltype_id`=?
                      AND `supporter`=TRUE
                      LIMIT 1
                ',
                [
                    $user['UID'],
                    $angeltype['id']
                ]
            )) > 0)
        || in_array('admin_user_angeltypes', $user['privileges']);
}

/**
 * Add or remove supporter rights.
 *
 * @param int  $user_angeltype_id
 * @param bool $supporter
 * @return int
 */
function UserAngelType_update($user_angeltype_id, $supporter)
{
    $result = DB::update('
      UPDATE `UserAngelTypes`
      SET `supporter`=?
      WHERE `id`=?
      LIMIT 1
    ', [$supporter, $user_angeltype_id]);

    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to update supporter rights.');
    }

    return $result;
}

/**
 * Delete all unconfirmed UserAngelTypes for given Angeltype.
 *
 * @param int $angeltype_id
 * @return bool
 */
function UserAngelTypes_delete_all($angeltype_id)
{
    DB::delete('
      DELETE FROM `UserAngelTypes`
      WHERE `angeltype_id`=?
      AND `confirm_user_id` IS NULL
    ', [$angeltype_id]);

    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to delete all unconfirmed users.');
    }

    return true;
}

/**
 * Confirm all unconfirmed UserAngelTypes for given Angeltype.
 *
 * @param int   $angeltype_id
 * @param array $confirm_user
 * @return bool
 */
function UserAngelTypes_confirm_all($angeltype_id, $confirm_user)
{
    $result = DB::update('
      UPDATE `UserAngelTypes`
      SET `confirm_user_id`=?
      WHERE `angeltype_id`=?
      AND `confirm_user_id` IS NULL
    ', [$confirm_user['UID'], $angeltype_id]);

    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to confirm all users.');
    }

    return (bool)$result;
}

/**
 * Confirm an UserAngelType with confirming user.
 *
 * @param int   $user_angeltype_id
 * @param array $confirm_user
 * @return bool
 */
function UserAngelType_confirm($user_angeltype_id, $confirm_user)
{
    $result = DB::update('
      UPDATE `UserAngelTypes`
      SET `confirm_user_id`=?
      WHERE `id`=?
      LIMIT 1', [$confirm_user['UID'], $user_angeltype_id]);
    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to confirm user angeltype.');
    }
    return (bool)$result;
}

/**
 * Delete an UserAngelType.
 *
 * @param array $user_angeltype
 * @return bool
 */
function UserAngelType_delete($user_angeltype)
{
    return (bool)DB::delete('
      DELETE FROM `UserAngelTypes`
      WHERE `id`=?
      LIMIT 1', [$user_angeltype['id']]);
}

/**
 * Create an UserAngelType.
 *
 * @param array $user
 * @param array $angeltype
 * @return int
 */
function UserAngelType_create($user, $angeltype)
{
    DB::insert('
            INSERT INTO `UserAngelTypes` (`user_id`, `angeltype_id`)
            VALUES (?, ?)
        ',
        [
            $user['UID'],
            $angeltype['id']
        ]
    );

    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to create user angeltype.');
    }

    return DB::getPdo()->lastInsertId();
}

/**
 * Get an UserAngelType by its id.
 *
 * @param int $user_angeltype_id
 * @return array|null
 */
function UserAngelType($user_angeltype_id)
{
    $angeltype = DB::select('
      SELECT *
      FROM `UserAngelTypes`
      WHERE `id`=?
      LIMIT 1', [$user_angeltype_id]);

    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to load user angeltype.');
    }

    if (empty($angeltype)) {
        return null;
    }

    return $angeltype[0];
}

/**
 * Get an UserAngelType by user and angeltype.
 *
 * @param array $user
 * @param array $angeltype
 * @return array|null
 */
function UserAngelType_by_User_and_AngelType($user, $angeltype)
{
    $angeltype = DB::select('
          SELECT *
          FROM `UserAngelTypes`
          WHERE `user_id`=?
          AND `angeltype_id`=?
          LIMIT 1
        ',
        [
            $user['UID'],
            $angeltype['id']
        ]
    );

    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to load user angeltype.');
    }

    if (empty($angeltype)) {
        return null;
    }

    return array_shift($angeltype);
}
