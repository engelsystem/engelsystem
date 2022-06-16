<?php

use Engelsystem\Database\Db;
use Engelsystem\Models\User\User;

/**
 * User angeltypes model
 */

/**
 * Checks if a user joined an angeltype.
 *
 * @param int   $userId    The user to be checked
 * @param array $angeltype The angeltype to be checked
 * @return boolean
 */
function UserAngelType_exists($userId, $angeltype)
{
    return count(Db::select('
        SELECT `id`
        FROM `UserAngelTypes`
        WHERE `UserAngelTypes`.`user_id`=?
        AND `angeltype_id`=?
        ', [$userId, $angeltype['id']])) > 0;
}

/**
 * List users angeltypes.
 *
 * @param int $userId
 * @return array[]
 */
function User_angeltypes($userId)
{
    return Db::select('
        SELECT `AngelTypes`.*, `UserAngelTypes`.`confirm_user_id`, `UserAngelTypes`.`supporter`
        FROM `UserAngelTypes`
        JOIN `AngelTypes` ON `UserAngelTypes`.`angeltype_id` = `AngelTypes`.`id`
        WHERE `UserAngelTypes`.`user_id`=?
        ', [$userId]);
}

/**
 * Gets unconfirmed user angeltypes for angeltypes of which the given user is a supporter.
 *
 * @param int $userId
 * @return array[]
 */
function User_unconfirmed_AngelTypes($userId)
{
    return Db::select('
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
        GROUP BY `UserAngelTypes`.`angeltype_id`, `UserAngelTypes`.`id`, AngelTypes.name, UserAngelTypes.user_id, UserAngelTypes.confirm_user_id, UserAngelTypes.supporter
        ORDER BY `AngelTypes`.`name`
    ', [$userId]);
}

/**
 * Returns true if user is angeltype supporter or has privilege admin_user_angeltypes.
 *
 * @param User  $user
 * @param array $angeltype
 * @return bool
 */
function User_is_AngelType_supporter($user, $angeltype)
{
    if (!$user) {
        return false;
    }

    $privileges = privileges_for_user($user->id);

    return (count(Db::select('
                    SELECT `id`
                    FROM `UserAngelTypes`
                    WHERE `user_id`=?
                    AND `angeltype_id`=?
                    AND `supporter`=TRUE
                    LIMIT 1
                ',
                [
                    $user->id,
                    $angeltype['id']
                ]
            )) > 0)
        || in_array('admin_user_angeltypes', $privileges);
}

/**
 * Add or remove supporter rights.
 *
 * @param int  $user_angeltype_id
 * @param bool $supporter
 */
function UserAngelType_update($user_angeltype_id, $supporter)
{
    Db::update('
        UPDATE `UserAngelTypes`
        SET `supporter`=?
        WHERE `id`=?
        LIMIT 1
    ', [(int)$supporter, $user_angeltype_id]);
}

/**
 * Delete all unconfirmed UserAngelTypes for given Angeltype.
 *
 * @param int $angeltype_id
 */
function UserAngelTypes_delete_all($angeltype_id)
{
    Db::delete('
        DELETE FROM `UserAngelTypes`
        WHERE `angeltype_id`=?
        AND `confirm_user_id` IS NULL
    ', [$angeltype_id]);
}

/**
 * Confirm all unconfirmed UserAngelTypes for given Angeltype.
 *
 * @param int $angeltype_id
 * @param int $confirm_user_id
 */
function UserAngelTypes_confirm_all($angeltype_id, $confirm_user_id)
{
    Db::update('
        UPDATE `UserAngelTypes`
        SET `confirm_user_id`=?
        WHERE `angeltype_id`=?
        AND `confirm_user_id` IS NULL
    ', [$confirm_user_id, $angeltype_id]);
}

/**
 * Get all unconfirmed Users for given Angeltype
 *
 * @param int $angeltype_id
 */
function UserAngelTypes_all_unconfirmed($angeltype_id)
{
    return Db::select('
        SELECT *
        FROM `UserAngelTypes`
        WHERE `angeltype_id`=?
        AND `confirm_user_id` IS NULL
    ', [$angeltype_id]);
}

/**
 * Confirm an UserAngelType with confirming user.
 *
 * @param int $user_angeltype_id
 * @param int $confirm_user_id
 */
function UserAngelType_confirm($user_angeltype_id, $confirm_user_id)
{
    Db::update('
        UPDATE `UserAngelTypes`
        SET `confirm_user_id`=?
        WHERE `id`=?
        LIMIT 1', [$confirm_user_id, $user_angeltype_id]);
}

/**
 * Delete an UserAngelType.
 *
 * @param array $user_angeltype
 */
function UserAngelType_delete($user_angeltype)
{
    Db::delete('
        DELETE FROM `UserAngelTypes`
        WHERE `id`=?
        LIMIT 1', [$user_angeltype['id']]);
}

/**
 * Create an UserAngelType.
 *
 * @param int   $userId
 * @param array $angeltype
 * @return int
 */
function UserAngelType_create($userId, $angeltype)
{
    Db::insert('
            INSERT INTO `UserAngelTypes` (`user_id`, `angeltype_id`, `supporter`)
            VALUES (?, ?, FALSE)
        ',
        [
            $userId,
            $angeltype['id']
        ]
    );

    return Db::getPdo()->lastInsertId();
}

/**
 * Get an UserAngelType by its id.
 *
 * @param int $user_angeltype_id
 * @return array|null
 */
function UserAngelType($user_angeltype_id)
{
    $angelType = Db::selectOne('
        SELECT *
        FROM `UserAngelTypes`
        WHERE `id`=?
        LIMIT 1', [$user_angeltype_id]);

    return empty($angelType) ? null : $angelType;
}

/**
 * Get an UserAngelType by user and angeltype.
 *
 * @param int   $userId
 * @param array $angeltype
 * @return array|null
 */
function UserAngelType_by_User_and_AngelType($userId, $angeltype)
{
    $angelType = Db::selectOne('
            SELECT *
            FROM `UserAngelTypes`
            WHERE `user_id`=?
            AND `angeltype_id`=?
            LIMIT 1
        ',
        [
            $userId,
            $angeltype['id']
        ]
    );

    return empty($angelType) ? null : $angelType;
}

/**
 * Get an UserAngelTypes by user
 *
 * @param int $userId
 * @param bool $onlyConfirmed
 * @return array[]|null
 */
function UserAngelTypes_by_User($userId, $onlyConfirmed=false)
{
    return Db::select(
        '
            SELECT *
            FROM `UserAngelTypes`
            ' . ($onlyConfirmed ? 'LEFT JOIN AngelTypes AS a ON a.id=UserAngelTypes.angeltype_id' : '') . '
            WHERE `user_id`=?
        '
        . (
        $onlyConfirmed ? 'AND (
                a.`restricted`=0
                OR (
                    NOT `UserAngelTypes`.`confirm_user_id` IS NULL
                    OR `UserAngelTypes`.`id` IS NULL
                )
            )' : ''
        ),
        [$userId]
    );
}
