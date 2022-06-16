<?php

use Engelsystem\Database\Db;
use Engelsystem\ValidationResult;

/**
 * Returns an array containing the basic attributes of angeltypes.
 * FIXME! This is the big sign for needing entity objects
 */
function AngelType_new()
{
    return [
        'id'                      => null,
        'name'                    => '',
        'restricted'              => false,
        'no_self_signup'          => false,
        'description'             => '',
        'requires_driver_license' => false,
        'contact_name'            => null,
        'contact_dect'            => null,
        'contact_email'           => null,
        'show_on_dashboard'       => true
    ];
}

/**
 * Checks if the angeltype has any contact information.
 *
 * @param array $angeltype Angeltype
 * @return bool
 */
function AngelType_has_contact_info($angeltype)
{
    return !empty($angeltype['contact_name'])
        || !empty($angeltype['contact_dect'])
        || !empty($angeltype['contact_email']);
}

/**
 * Delete an Angeltype.
 *
 * @param array $angeltype
 */
function AngelType_delete($angeltype)
{
    Db::delete('
        DELETE FROM `AngelTypes`
        WHERE `id`=?
        LIMIT 1
    ', [$angeltype['id']]);
    engelsystem_log('Deleted angeltype: ' . AngelType_name_render($angeltype, true));
}

/**
 * Update Angeltype.
 *
 * @param array $angeltype The angeltype
 */
function AngelType_update($angeltype)
{
    Db::update('
            UPDATE `AngelTypes` SET
            `name` = ?,
            `restricted` = ?,
            `description` = ?,
            `requires_driver_license` = ?,
            `no_self_signup` = ?,
            `contact_name` = ?,
            `contact_dect` = ?,
            `contact_email` = ?,
            `show_on_dashboard` = ?
            WHERE `id` = ?
        ',
        [
            $angeltype['name'],
            (int)$angeltype['restricted'],
            $angeltype['description'],
            (int)$angeltype['requires_driver_license'],
            (int)$angeltype['no_self_signup'],
            $angeltype['contact_name'],
            $angeltype['contact_dect'],
            $angeltype['contact_email'],
            (int)$angeltype['show_on_dashboard'],
            $angeltype['id'],
        ]
    );

    engelsystem_log(
        'Updated angeltype: ' . $angeltype['name'] . ($angeltype['restricted'] ? ', restricted' : '')
        . ($angeltype['no_self_signup'] ? ', no_self_signup' : '')
        . ($angeltype['requires_driver_license'] ? ', requires driver license' : '') . ', '
        . $angeltype['contact_name'] . ', '
        . $angeltype['contact_dect'] . ', '
        . $angeltype['contact_email'] . ', '
        . $angeltype['show_on_dashboard']
    );
}

/**
 * Create an Angeltype.
 *
 * @param array $angeltype The angeltype
 * @return array the created angeltype
 */
function AngelType_create($angeltype)
{
    Db::insert('
            INSERT INTO `AngelTypes` (
                `name`,
                `restricted`,
                `description`,
                `requires_driver_license`,
                `no_self_signup`,
                `contact_name`,
                `contact_dect`,
                `contact_email`,
                `show_on_dashboard`
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ',
        [
            $angeltype['name'],
            (int)$angeltype['restricted'],
            $angeltype['description'],
            (int)$angeltype['requires_driver_license'],
            (int)$angeltype['no_self_signup'],
            $angeltype['contact_name'],
            $angeltype['contact_dect'],
            $angeltype['contact_email'],
            (int)$angeltype['show_on_dashboard']
        ]
    );

    $angeltype['id'] = Db::getPdo()->lastInsertId();
    engelsystem_log(
        'Created angeltype: ' . $angeltype['name']
        . ($angeltype['restricted'] ? ', restricted' : '')
        . ($angeltype['requires_driver_license'] ? ', requires driver license' : '') . ', '
        . $angeltype['contact_name'] . ', '
        . $angeltype['contact_dect'] . ', '
        . $angeltype['contact_email'] . ', '
        . $angeltype['show_on_dashboard']
    );

    return $angeltype;
}

/**
 * Validates a name for angeltypes.
 * Returns ValidationResult containing validation success and validated name.
 *
 * @param string $name      Wanted name for the angeltype
 * @param array  $angeltype The angeltype the name is for
 *
 * @return ValidationResult result and validated name
 */
function AngelType_validate_name($name, $angeltype)
{
    $name = strip_item($name);
    if ($name == '') {
        return new ValidationResult(false, '');
    }
    if (!empty($angeltype) && isset($angeltype['id'])) {
        $valid = (count(Db::select('
            SELECT `id`
            FROM `AngelTypes`
            WHERE `name`=?
            AND NOT `id`=?
            LIMIT 1
        ', [$name, $angeltype['id']])) == 0);
        return new ValidationResult($valid, $name);
    }
    $valid = (count(Db::select('
        SELECT `id`
        FROM `AngelTypes`
        WHERE `name`=?
        LIMIT 1', [$name])) == 0);

    return new ValidationResult($valid, $name);
}

/**
 * Returns all angeltypes and subscription state to each of them for given user.
 *
 * @param int $userId
 * @return array
 */
function AngelTypes_with_user($userId)
{
    return Db::select('
        SELECT `AngelTypes`.*,
        `UserAngelTypes`.`id` AS `user_angeltype_id`,
        `UserAngelTypes`.`confirm_user_id`,
        `UserAngelTypes`.`supporter`
        FROM `AngelTypes`
        LEFT JOIN `UserAngelTypes` ON `AngelTypes`.`id`=`UserAngelTypes`.`angeltype_id`
        AND `UserAngelTypes`.`user_id` = ?
        ORDER BY `name`', [$userId]);
}

/**
 * Returns all angeltypes.
 *
 * @return array
 */
function AngelTypes()
{
    return Db::select('
        SELECT *
        FROM `AngelTypes`
        ORDER BY `name`
    ');
}

/**
 * Returns AngelType id array
 *
 * @return array
 */
function AngelType_ids()
{
    $result = Db::select('SELECT `id` FROM `AngelTypes`');
    return select_array($result, 'id', 'id');
}

/**
 * Returns angelType by id.
 *
 * @param int $angeltype_id angelType ID
 * @return array|null
 */
function AngelType($angeltype_id)
{
    $angelType = Db::selectOne(
        'SELECT * FROM `AngelTypes` WHERE `id`=?',
        [$angeltype_id]
    );

    return empty($angelType) ? null : $angelType;
}
