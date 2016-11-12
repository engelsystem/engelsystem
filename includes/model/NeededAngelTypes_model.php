<?php

/**
 * Entity needed angeltypes describes how many angels of given type are needed for a shift or in a room.
 */

/**
 * Insert a new needed angel type.
 *
 * @param int $shift_id
 *          The shift. Can be null, but then a room_id must be given.
 * @param int $angeltype_id
 *          The angeltype
 * @param int $room_id
 *          The room. Can be null, but then a shift_id must be given.
 * @param int $count
 *          How many angels are needed?
 */
function NeededAngelType_add($shift_id, $angeltype_id, $room_id, $count) {
  $result = sql_query("
      INSERT INTO `NeededAngelTypes` SET 
      `shift_id`=" . sql_null($shift_id) . ", 
      `angel_type_id`='" . sql_escape($angeltype_id) . "', 
      `room_id`=" . sql_null($room_id) . ",
      `count`='" . sql_escape($count) . "'");
  if ($result === false) {
    return false;
  }
  return sql_id();
}

/**
 * Deletes all needed angel types from given shift.
 *
 * @param int $shift_id
 *          id of the shift
 */
function NeededAngelTypes_delete_by_shift($shift_id) {
  return sql_query("DELETE FROM `NeededAngelTypes` WHERE `shift_id`='" . sql_escape($shift_id) . "'");
}

/**
 * Deletes all needed angel types from given room.
 *
 * @param int $room_id
 *          id of the room
 */
function NeededAngelTypes_delete_by_room($room_id) {
  return sql_query("DELETE FROM `NeededAngelTypes` WHERE `room_id`='" . sql_escape($room_id) . "'");
}

/**
 * Returns all needed angeltypes and already taken needs.
 *
 * @param int $shiftID
 *          id of shift
 */
function NeededAngelTypes_by_shift($shiftId) {
  $needed_angeltypes_source = sql_select("
        SELECT `NeededAngelTypes`.*, `AngelTypes`.`id`, `AngelTypes`.`name`, `AngelTypes`.`restricted`
        FROM `NeededAngelTypes`
        JOIN `AngelTypes` ON `AngelTypes`.`id` = `NeededAngelTypes`.`angel_type_id`
        WHERE `shift_id`='" . sql_escape($shiftId) . "'
        AND `count` > 0
        ORDER BY `room_id` DESC
        ");
  if ($needed_angeltypes_source === false) {
    engelsystem_error("Unable to load needed angeltypes.");
  }
  
  // Use settings from room
  if (count($needed_angeltypes_source) == 0) {
    $needed_angeltypes_source = sql_select("
        SELECT `NeededAngelTypes`.*, `AngelTypes`.`name`, `AngelTypes`.`restricted`
        FROM `NeededAngelTypes`
        JOIN `AngelTypes` ON `AngelTypes`.`id` = `NeededAngelTypes`.`angel_type_id`
        JOIN `Shifts` ON `Shifts`.`RID` = `NeededAngelTypes`.`room_id`
        WHERE `Shifts`.`SID`='" . sql_escape($shiftId) . "'
        AND `count` > 0
        ORDER BY `room_id` DESC
        ");
    if ($needed_angeltypes_source === false) {
      engelsystem_error("Unable to load needed angeltypes.");
    }
  }
  
  $needed_angeltypes = [];
  foreach ($needed_angeltypes_source as $angeltype) {
    $shift_entries = ShiftEntries_by_shift_and_angeltype($shiftId, $angeltype['angel_type_id']);
    
    $angeltype['taken'] = 0;
    foreach($shift_entries as $shift_entry) {
      if($shift_entry['freeloaded'] == 0) {
        $angeltype['taken']++;
      }
    }
    
    $angeltype['shift_entries'] = $shift_entries;
    $needed_angeltypes[] = $angeltype;
  }
  
  return $needed_angeltypes;
}

?>