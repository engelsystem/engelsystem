<?php
function Shifts_by_user($user) {
  return sql_select("
      SELECT * 
      FROM `ShiftEntry` 
      JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`) 
      JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) 
      WHERE `UID`=" . sql_escape($user['UID']) . " 
      ORDER BY `start`
      ");
}

/**
 * TODO: $_REQUEST is not allowed in model!
 * Returns Shift id array
 */
function Shifts_filtered() {
  global $_REQUEST;
  $filter = "";
  
  // filterRoom (Array of integer) - Array of Room IDs (optional, for list request)
  if (isset($_REQUEST['filterRoom']) && is_array($_REQUEST['filterRoom'])) {
    foreach ($_REQUEST['filterRoom'] as $key => $value) {
      $filter .= ", `RID`=" . sql_escape($value) . " ";
    }
  }
  
  // filterTask (Array of integer) - Array if Task (optional, for list request)
  if (isset($_REQUEST['filterTask']) && is_array($_REQUEST['filterTask'])) {
    foreach ($_REQUEST['filterTask'] as $key => $value) {
      // TODO $filter .= ", `RID`=" . sql_escape($value) . " ";
    }
  }
  
  // filterOccupancy (integer) - Occupancy state: (optional, for list request)
  // 1 occupied, 2 free, 3 occupied and free
  if (isset($_REQUEST['filterOccupancy']) && is_array($_REQUEST['filterOccupancy'])) {
    foreach ($_REQUEST['filterOccupancy'] as $key => $value) {
      // TODO $filter .= ", `RID`=" . sql_escape($value) . " ";
    }
  }
  
  // format filter
  if ($filter != "") {
    $filter = ' WHERE ' . substr($filter, 1);
  }
  
  // real request
  $shifts_source = sql_select("SELECT `SID` FROM `Shifts`" . $filter);
  if ($shifts_source === false)
    return false;
  if (count($shifts_source) > 0) {
    return $shifts_source;
  }
  return null;
}

/**
 * Returns Shift by id.
 *
 * @param $id Shift
 *          ID
 */
function Shift($id) {
  $shifts_source = sql_select("SELECT * FROM `Shifts` WHERE `SID`=" . sql_escape($id) . " LIMIT 1");
  $shiftsEntry_source = sql_select("SELECT `TID` , `UID` , `freeloaded` FROM `ShiftEntry` WHERE `SID`=" . sql_escape($id));
  
  if ($shifts_source === false)
    return false;
  if (count($shifts_source) > 0) {
    $result = $shifts_source[0];
    
    $result['ShiftEntry'] = $shiftsEntry_source;
    
    $temp = NeededAngelTypes_by_shift($id);
    foreach ($temp as $e) {
      $result['NeedAngels'][] = array(
          'TID' => $e['angel_type_id'],
          'count' => $e['count'],
          'restricted' => $e['restricted'],
          'taken' => $e['taken'] 
      );
    }
    
    return $result;
  }
  return null;
}

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
    $needed_angeltypes = NeededAngelTypes_by_shift($shift['SID']);
    if ($needed_angeltypes === false)
      return false;
    
    $shift['angeltypes'] = $needed_angeltypes;
  }
  
  return $shifts_source;
}

?>