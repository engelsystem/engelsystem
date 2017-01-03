<?php

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
    return $user_driver_license['has_car']
        || $user_driver_license['has_license_car']
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
    $user_driver_license = sql_select("SELECT * FROM `UserDriverLicenses` WHERE `user_id`='" . sql_escape($user_id) . "'");
    if ($user_driver_license === false) {
        engelsystem_error('Unable to load user driver license.');
        return false;
    }

    if (count($user_driver_license) == 0) {
        return null;
    }

    return $user_driver_license[0];
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
    $result = sql_query("
      INSERT INTO `UserDriverLicenses` SET
      `user_id`=" . sql_escape($user_driver_license['user_id']) . ",
      `has_car`=" . sql_bool($user_driver_license['has_car']) . ",
      `has_license_car`=" . sql_bool($user_driver_license['has_license_car']) . ",
      `has_license_3_5t_transporter`=" . sql_bool($user_driver_license['has_license_3_5t_transporter']) . ",
      `has_license_7_5t_truck`=" . sql_bool($user_driver_license['has_license_7_5t_truck']) . ",
      `has_license_12_5t_truck`=" . sql_bool($user_driver_license['has_license_12_5t_truck']) . ",
      `has_license_forklift`=" . sql_bool($user_driver_license['has_license_forklift']));
    if ($result === false) {
        engelsystem_error('Unable to create user driver license');
    }
    return $user_driver_license;
}

/**
 * Update a user's driver license entry
 *
 * @param array $user_driver_license The UserDriverLicense to update
 * @return mysqli_result
 */
function UserDriverLicenses_update($user_driver_license)
{
    $result = sql_query("UPDATE `UserDriverLicenses` SET
      `has_car`=" . sql_bool($user_driver_license['has_car']) . ",
      `has_license_car`=" . sql_bool($user_driver_license['has_license_car']) . ",
      `has_license_3_5t_transporter`=" . sql_bool($user_driver_license['has_license_3_5t_transporter']) . ",
      `has_license_7_5t_truck`=" . sql_bool($user_driver_license['has_license_7_5t_truck']) . ",
      `has_license_12_5t_truck`=" . sql_bool($user_driver_license['has_license_12_5t_truck']) . ",
      `has_license_forklift`=" . sql_bool($user_driver_license['has_license_forklift']) . "
      WHERE `user_id`='" . sql_escape($user_driver_license['user_id']) . "'");
    if ($result === false) {
        engelsystem_error('Unable to update user driver license information');
    }
    return $result;
}

/**
 * Delete a user's driver license entry
 *
 * @param int $user_id
 * @return mysqli_result
 */
function UserDriverLicenses_delete($user_id)
{
    $result = sql_query("DELETE FROM `UserDriverLicenses` WHERE `user_id`=" . sql_escape($user_id));
    if ($result === false) {
        engelsystem_error('Unable to remove user driver license information');
    }
    return $result;
}
