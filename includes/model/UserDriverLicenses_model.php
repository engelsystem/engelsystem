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
 * @return array|null
 */
function UserDriverLicense($user_id)
{
    $driverLicense = DB::selectOne('
        SELECT *
        FROM `UserDriverLicenses`
        WHERE `user_id`=?', [$user_id]);

    return empty($driverLicense) ? null : $driverLicense;
}

/**
 * Create a user's driver license entry
 *
 * @param array $user_driver_license The UserDriverLicense to create
 * @param int   $userId
 * @return array
 */
function UserDriverLicenses_create($user_driver_license, $userId)
{
    $user_driver_license['user_id'] = $userId;
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
            (int)$user_driver_license['has_car'],
            (int)$user_driver_license['has_license_car'],
            (int)$user_driver_license['has_license_3_5t_transporter'],
            (int)$user_driver_license['has_license_7_5t_truck'],
            (int)$user_driver_license['has_license_12_5t_truck'],
            (int)$user_driver_license['has_license_forklift'],
        ]
    );

    return $user_driver_license;
}

/**
 * Update a user's driver license entry
 *
 * @param array $user_driver_license The UserDriverLicense to update
 */
function UserDriverLicenses_update($user_driver_license)
{
    DB::update('
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
            (int)$user_driver_license['has_car'],
            (int)$user_driver_license['has_license_car'],
            (int)$user_driver_license['has_license_3_5t_transporter'],
            (int)$user_driver_license['has_license_7_5t_truck'],
            (int)$user_driver_license['has_license_12_5t_truck'],
            (int)$user_driver_license['has_license_forklift'],
            $user_driver_license['user_id'],
        ]
    );
}

/**
 * Delete a user's driver license entry
 *
 * @param int $user_id
 */
function UserDriverLicenses_delete($user_id)
{
    DB::delete('DELETE FROM `UserDriverLicenses` WHERE `user_id`=?', [$user_id]);
}
