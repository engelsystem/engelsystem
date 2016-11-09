<?php

/**
 * Delete a shift type.
 * @param int $shifttype_id
 */
function ShiftType_delete($shifttype_id) {
  return sql_query("DELETE FROM `ShiftTypes` WHERE `id`='" . sql_escape($shifttype_id) . "'");
}

/**
 * Update a shift type.
 *
 * @param int $shifttype_id          
 * @param string $name          
 * @param int $angeltype_id          
 * @param string $description          
 */
function ShiftType_update($shifttype_id, $name, $angeltype_id, $description) {
  return sql_query("UPDATE `ShiftTypes` SET
      `name`='" . sql_escape($name) . "', 
      `angeltype_id`=" . sql_null($angeltype_id) . ",
      `description`='" . sql_escape($description) . "'
      WHERE `id`='" . sql_escape($shifttype_id) . "'");
}

/**
 * Create a shift type.
 *
 * @param string $name          
 * @param int $angeltype_id          
 * @param string $description          
 * @return new shifttype id
 */
function ShiftType_create($name, $angeltype_id, $description) {
  $result = sql_query("INSERT INTO `ShiftTypes` SET
      `name`='" . sql_escape($name) . "', 
      `angeltype_id`=" . sql_null($angeltype_id) . ",
      `description`='" . sql_escape($description) . "'");
  if ($result === false) {
    return false;
  }
  return sql_id();
}

/**
 * Get a shift type by id.
 *
 * @param int $shifttype_id          
 */
function ShiftType($shifttype_id) {
  $shifttype = sql_select("SELECT * FROM `ShiftTypes` WHERE `id`='" . sql_escape($shifttype_id) . "'");
  if ($shifttype === false) {
    engelsystem_error('Unable to load shift type.');
  }
  if ($shifttype == null) {
    return null;
  }
  return $shifttype[0];
}

/**
 * Get all shift types.
 */
function ShiftTypes() {
  return sql_select("SELECT * FROM `ShiftTypes` ORDER BY `name`");
}

?>