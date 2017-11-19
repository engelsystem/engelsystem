<?php

use Engelsystem\Database\DB;
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
        'contact_user_id'         => null,
        'contact_name'            => null,
        'contact_dect'            => null,
        'contact_email'           => null
    ];
}

/**
 * Validates the contact user
 *
 * @param array $angeltype The angeltype
 * @return ValidationResult
 */
function AngelType_validate_contact_user_id($angeltype)
{
    if (!isset($angeltype['contact_user_id'])) {
        return new ValidationResult(true, null);
    }
    if (isset($angeltype['contact_name']) || isset($angeltype['contact_dect']) || isset($angeltype['contact_email'])) {
        return new ValidationResult(false, $angeltype['contact_user_id']);
    }
    if (User($angeltype['contact_user_id']) == null) {
        return new ValidationResult(false, $angeltype['contact_user_id']);
    }
    return new ValidationResult(true, $angeltype['contact_user_id']);
}

/**
 * Returns contact data (name, dect, email) for given angeltype or null
 *
 * @param array $angeltype The angeltype
 * @return array|null
 */
function AngelType_contact_info($angeltype)
{
    if (isset($angeltype['contact_user_id'])) {
        $contact_user = User($angeltype['contact_user_id']);
        $contact_data = [
            'contact_name' => $contact_user['Nick'],
            'contact_dect' => $contact_user['DECT']
        ];
        if ($contact_user['email_by_human_allowed']) {
            $contact_data['contact_email'] = $contact_user['email'];
        }
        return $contact_data;
    }
    if (isset($angeltype['contact_name'])) {
        return [
            'contact_name'  => $angeltype['contact_name'],
            'contact_dect'  => $angeltype['contact_dect'],
            'contact_email' => $angeltype['contact_email']
        ];
    }

    return null;
}

/**
 * Delete an Angeltype.
 *
 * @param array $angeltype
 */
function AngelType_delete($angeltype)
{
    DB::delete('
      DELETE FROM `AngelTypes`
      WHERE `id`=?
      LIMIT 1
    ', [$angeltype['id']]);
    engelsystem_log('Deleted angeltype: ' . AngelType_name_render($angeltype));
}

/**
 * Update Angeltype.
 *
 * @param array $angeltype The angeltype
 */
function AngelType_update($angeltype)
{
    DB::update('
          UPDATE `AngelTypes` SET
          `name` = ?,
          `restricted` = ?,
          `description` = ?,
          `requires_driver_license` = ?,
          `no_self_signup` = ?,
          `contact_user_id` = ?,
          `contact_name` = ?,
          `contact_dect` = ?,
          `contact_email` = ?
          WHERE `id` = ?',
        [
            $angeltype['name'],
            (int)$angeltype['restricted'],
            $angeltype['description'],
            (int)$angeltype['requires_driver_license'],
            (int)$angeltype['no_self_signup'],
            $angeltype['contact_user_id'],
            $angeltype['contact_name'],
            $angeltype['contact_dect'],
            $angeltype['contact_email'],
            $angeltype['id'],
        ]
    );

    engelsystem_log(
        'Updated angeltype: ' . $angeltype['name'] . ($angeltype['restricted'] ? ', restricted' : '')
        . ($angeltype['no_self_signup'] ? ', no_self_signup' : '')
        . ($angeltype['requires_driver_license'] ? ', requires driver license' : '')
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
    DB::insert('
          INSERT INTO `AngelTypes` (
              `name`,
              `restricted`,
              `description`,
              `requires_driver_license`,
              `no_self_signup`,
              `contact_user_id`,
              `contact_name`,
              `contact_dect`,
              `contact_email`
          )
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ',
        [
            $angeltype['name'],
            (int)$angeltype['restricted'],
            $angeltype['description'],
            (int)$angeltype['requires_driver_license'],
            (int)$angeltype['no_self_signup'],
            $angeltype['contact_user_id'],
            $angeltype['contact_name'],
            $angeltype['contact_dect'],
            $angeltype['contact_email'],
        ]
    );

    $angeltype['id'] = DB::getPdo()->lastInsertId();
    engelsystem_log(
        'Created angeltype: ' . $angeltype['name']
        . ($angeltype['restricted'] ? ', restricted' : '')
        . ($angeltype['requires_driver_license'] ? ', requires driver license' : '')
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
    if ($angeltype != null && isset($angeltype['id'])) {
        $valid = (count(DB::select('
            SELECT `id`
            FROM `AngelTypes`
            WHERE `name`=?
            AND NOT `id`=?
            LIMIT 1
        ', [$name, $angeltype['id']])) == 0);
        return new ValidationResult($valid, $name);
    }
    $valid = (count(DB::select('
        SELECT `id`
        FROM `AngelTypes`
        WHERE `name`=?
        LIMIT 1', [$name])) == 0);
    return new ValidationResult($valid, $name);
}

/**
 * Returns all angeltypes and subscription state to each of them for given user.
 *
 * @param array $user
 * @return array
 */
function AngelTypes_with_user($user)
{
    return DB::select('
      SELECT `AngelTypes`.*,
      `UserAngelTypes`.`id` AS `user_angeltype_id`,
      `UserAngelTypes`.`confirm_user_id`,
      `UserAngelTypes`.`supporter`
      FROM `AngelTypes`
      LEFT JOIN `UserAngelTypes` ON `AngelTypes`.`id`=`UserAngelTypes`.`angeltype_id`
      AND `UserAngelTypes`.`user_id` = ?
      ORDER BY `name`', [$user['UID']]);
}

/**
 * Returns all angeltypes.
 *
 * @return array
 */
function AngelTypes()
{
    return DB::select('
      SELECT *
      FROM `AngelTypes`
      ORDER BY `name`');
}

/**
 * Returns AngelType id array
 *
 * @return array
 */
function AngelType_ids()
{
    $result = DB::select('SELECT `id` FROM `AngelTypes`');
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
    return DB::selectOne(
        'SELECT * FROM `AngelTypes` WHERE `id`=?',
        [$angeltype_id]
    );
}
