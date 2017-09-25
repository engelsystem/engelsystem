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
 * @return array
 */
function User_angeltypes($user)
{
    return DB::select('
      SELECT `AngelTypes`.*, `UserAngelTypes`.`confirm_user_id`, `UserAngelTypes`.`supporter`
      FROM `UserAngelTypes`
      JOIN `AngelTypes` ON `UserAngelTypes`.`angeltype_id` = `AngelTypes`.`id`
      WHERE `UserAngelTypes`.`user_id`=?
      ', [$user['UID']]);
}

/**
 * Gets unconfirmed user angeltypes for angeltypes of which the given user is a supporter.
 *
 * @param array $user
 * @return array
 */
function User_unconfirmed_AngelTypes($user)
{
    return DB::select('
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
        GROUP BY `UserAngelTypes`.`angeltype_id`, `UserAngelTypes`.`id`
        ORDER BY `AngelTypes`.`name`
    ', [$user['UID']]);
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
 */
function UserAngelType_update($user_angeltype_id, $supporter)
{
    DB::update('
      UPDATE `UserAngelTypes`
      SET `supporter`=?
      WHERE `id`=?
      LIMIT 1
    ', [$supporter, $user_angeltype_id]);
}

/**
 * Delete all unconfirmed UserAngelTypes for given Angeltype.
 *
 * @param int $angeltype_id
 */
function UserAngelTypes_delete_all($angeltype_id)
{
    DB::delete('
      DELETE FROM `UserAngelTypes`
      WHERE `angeltype_id`=?
      AND `confirm_user_id` IS NULL
    ', [$angeltype_id]);
}

/**
 * Confirm all unconfirmed UserAngelTypes for given Angeltype.
 *
 * @param int   $angeltype_id
 * @param array $confirm_user
 */
function UserAngelTypes_confirm_all($angeltype_id, $confirm_user)
{
    DB::update('
      UPDATE `UserAngelTypes`
      SET `confirm_user_id`=?
      WHERE `angeltype_id`=?
      AND `confirm_user_id` IS NULL
    ', [$confirm_user['UID'], $angeltype_id]);
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
    DB::update('
      UPDATE `UserAngelTypes`
      SET `confirm_user_id`=?
      WHERE `id`=?
      LIMIT 1', [$confirm_user['UID'], $user_angeltype_id]);
}

/**
 * Delete an UserAngelType.
 *
 * @param array $user_angeltype
 */
function UserAngelType_delete($user_angeltype)
{
    DB::delete('
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
    return DB::selectOne('
      SELECT *
      FROM `UserAngelTypes`
      WHERE `id`=?
      LIMIT 1', [$user_angeltype_id]);
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
    return DB::selectOne('
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
}
