<?php

/**
 * Returns all shifts with needed angeltypes and count of subscribed jobs.
 */
function Shifts() {
  $shifts_source = sql_select("
    SELECT `Shifts`.*, `Room`.`RID`, `Room`.`Name` as `room_name` 
    FROM `Shifts`
    JOIN `Room` ON `Room`.`RID` = `Shifts`.`RID`
    ");
  if ($shifts_source === false)
    return false;
  
  foreach ($shifts_source as &$shift) {
    $needed_angeltypes = NeededAngelTypes_by_shift($shift);
    if ($needed_angeltypes === false)
      return false;

    $shift['angeltypes'] = $needed_angeltypes;
  }
  
  return $shifts_source;
}

?>