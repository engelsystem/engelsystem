<?php

use Engelsystem\Database\DB;

/**
 * Returns a new empty UserDriverLicense
 * FIXME entity object needed
 *
 * @return array
 */
function UserDriverLicense_new()
{
    return [
        'user_id'                      => null,
        'has_car'                      => false,
        'has_license_car'              => false,
        'has_license_3_5t_transporter' => false,
        'has_license_7_5t_truck'       => false,
        'has_license_12_5t_truck'      => false,
        'has_license_forklift'         => false
    ];
}

/**
 * Is it valid?
 *
 * @param array $user_driver_license The UserDriverLicense to check
 * @return boolean
 */
function UserDriverLicense_valid($user_driver_license)
{
    return
        $user_driver_license['has_license_car']
        || $user_driver_license['has_license_3_5t_transporter']
        || $user_driver_license['has_license_7_5t_truck']
        || $user_driver_license['has_license_12_5t_truck']
        || $user_driver_license['has_license_forklift'];
}

/**
 * Get a users driver license information
 *
 * @param int $user_id The users id
 * @return array|false|null
 */
function UserDriverLicense($user_id)
{
    $user_driver_license = DB::select('
        SELECT *
        FROM `UserDriverLicenses`
        WHERE `user_id`=?', [$user_id]);

    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to load user driver license.');
        return false;
    }

    if (empty($user_driver_license)) {
        return null;
    }

    return array_shift($user_driver_license);
}

/**
 * Create a user's driver license entry
 *
 * @param array $user_driver_license The UserDriverLicense to create
 * @param array $user
 * @return array
 */
function UserDriverLicenses_create($user_driver_license, $user)
{
    $user_driver_license['user_id'] = $user['UID'];
    DB::insert('
          INSERT INTO `UserDriverLicenses` (
              `user_id`,
              `has_car`,
              `has_license_car`,
              `has_license_3_5t_transporter`,
              `has_license_7_5t_truck`,
              `has_license_12_5t_truck`,
              `has_license_forklift`
          )
          VALUES (?, ?, ?, ?, ?, ?, ?)
        ',
        [
            $user_driver_license['user_id'],
            (bool)$user_driver_license['has_car'],
            (bool)$user_driver_license['has_license_car'],
            (bool)$user_driver_license['has_license_3_5t_transporter'],
            (bool)$user_driver_license['has_license_7_5t_truck'],
            (bool)$user_driver_license['has_license_12_5t_truck'],
            (bool)$user_driver_license['has_license_forklift'],
        ]
    );
    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to create user driver license');
    }

    return $user_driver_license;
}

/**
 * Update a user's driver license entry
 *
 * @param array $user_driver_license The UserDriverLicense to update
 * @return bool
 */
function UserDriverLicenses_update($user_driver_license)
{
    $result = DB::update('
          UPDATE `UserDriverLicenses`
          SET
              `has_car`=?,
              `has_license_car`=?,
              `has_license_3_5t_transporter`=?,
              `has_license_7_5t_truck`=?,
              `has_license_12_5t_truck`=?,
              `has_license_forklift`=?
          WHERE `user_id`=?
       ',
        [
            (bool)$user_driver_license['has_car'],
            (bool)$user_driver_license['has_license_car'],
            (bool)$user_driver_license['has_license_3_5t_transporter'],
            (bool)$user_driver_license['has_license_7_5t_truck'],
            (bool)$user_driver_license['has_license_12_5t_truck'],
            (bool)$user_driver_license['has_license_forklift'],
            $user_driver_license['user_id'],
        ]
    );
    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to update user driver license information');
    }
    return $result;
}

/**
 * Delete a user's driver license entry
 *
 * @param int $user_id
 * @return bool
 */
function UserDriverLicenses_delete($user_id)
{
    $result = DB::delete('DELETE FROM `UserDriverLicenses` WHERE `user_id`=?', [$user_id]);
    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to remove user driver license information');
    }
    return $result;
}
