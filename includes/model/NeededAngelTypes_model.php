<?php

/**
 * Returns all needed angeltypes and already taken needs.
 *
 * @param shiftID id of shift
 */
function NeededAngelTypes_by_shift($shiftId) {
  $needed_angeltypes_source = sql_select("
        SELECT `NeededAngelTypes`.*, `AngelTypes`.`name`, `AngelTypes`.`restricted`
        FROM `NeededAngelTypes`
        JOIN `AngelTypes` ON `AngelTypes`.`id` = `NeededAngelTypes`.`angel_type_id`
        WHERE `shift_id`='" . sql_escape($shiftId) . "'
        AND `count` > 0
        ORDER BY `room_id` DESC
        ");
  if ($needed_angeltypes_source === false)
    return false;

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
    if ($needed_angeltypes_source === false)
      return false;
  }

  $needed_angeltypes = array();
  foreach ($needed_angeltypes_source as $angeltype) {
    $shift_entries = ShiftEntries_by_shift_and_angeltype($shiftId, $angeltype['angel_type_id']);
    if ($shift_entries === false)
      return false;

    $angeltype['taken'] = count($shift_entries);
    $needed_angeltypes[] = $angeltype;
  }

  return $needed_angeltypes;
}

?>