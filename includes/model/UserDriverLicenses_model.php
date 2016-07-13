<?php

/**
 * Get a users driver license information
 * @param int $user_id The users id
 */
function UserDriverLicense($user_id) {
  $user_driver_license = sql_select("SELECT * FROM `UserDriverLicenses` WHERE `user_id`='" . sql_escape($user_id) . "'");
  if ($user_driver_license === false)
    return false;
  if (count($user_driver_license) > 0)
    return $user_driver_license[0];
  return null;
}

/**
 * Create a user's driver license entry
 *
 * @param bool $user_id
 * @param bool $has_car
 * @param bool $has_license_car
 * @param bool $has_license_3_5t_transporter
 * @param bool $has_license_7_5t_truck
 * @param bool $has_license_12_5t_truck
 * @param bool $has_license_forklift
 */
function UserDriverLicenses_create($user_id, $has_car, $has_license_car, $has_license_3_5t_transporter, $has_license_7_5t_truck, $has_license_12_5t_truck, $has_license_forklift) {
  return sql_query("
      INSERT INTO `UserDriverLicenses` SET
      `user_id`=" . sql_escape($user_id) . ",
      `has_car`=" . sql_bool($has_car) . ",
      `has_license_car`=" . sql_bool($has_license_car) . ",
      `has_license_3_5t_transporter`=" . sql_bool($has_license_3_5t_transporter) . ",
      `has_license_7_5t_truck`=" . sql_bool($has_license_7_5t_truck) . ",
      `has_license_12_5t_truck`=" . sql_bool($has_license_12_5t_truck) . ",
      `has_license_forklift`=" . sql_bool($has_license_forklift));
}

/**
 * Update a user's driver license entry
 *
 * @param bool $user_id
 * @param bool $has_car
 * @param bool $has_license_car
 * @param bool $has_license_3_5t_transporter
 * @param bool $has_license_7_5t_truck
 * @param bool $has_license_12_5t_truck
 * @param bool $has_license_forklift
 */
function UserDriverLicenses_update($user_id, $has_car, $has_license_car, $has_license_3_5t_transporter, $has_license_7_5t_truck, $has_license_12_5t_truck, $has_license_forklift) {
  return sql_query("UPDATE `UserDriverLicenses` SET
      `has_car`=" . sql_bool($has_car) . ",
      `has_license_car`=" . sql_bool($has_license_car) . ",
      `has_license_3_5t_transporter`=" . sql_bool($has_license_3_5t_transporter) . ",
      `has_license_7_5t_truck`=" . sql_bool($has_license_7_5t_truck) . ",
      `has_license_12_5t_truck`=" . sql_bool($has_license_12_5t_truck) . ",
      `has_license_forklift`=" . sql_bool($has_license_forklift) . "
      WHERE `user_id`='" . sql_escape($user_id) . "'");
}

/**
 * Delete a user's driver license entry
 *
 * @param int $user_id
 */
function UserDriverLicenses_delete($user_id) {
  return sql_query("DELETE FROM `UserDriverLicenses` WHERE `user_id`=" . sql_escape($user_id));
}
?>