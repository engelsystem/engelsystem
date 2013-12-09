<?php

/**
 * Export all shifts using api-key.
 */
function shifts_json_export_all_controller() {
  global $api_key;
  
  if ($api_key == "")
    die("Config contains empty apikey.");
  
  if (! isset($_REQUEST['api_key']))
    die("Missing parameter api_key.");
  
  if ($_REQUEST['api_key'] != $api_key)
    die("Invalid api_key.");
  
  $shifts_source = Shifts();
  if ($shifts_source === false)
    die("Unable to load shifts.");
  
  header("Content-Type: application/json; charset=utf-8");
  echo json_encode($shifts_source);
  die();
}

/**
 * Export filtered shifts via JSON.
 * (Like iCal Export or shifts view)
 */
function shifts_json_export_controller() {
  global $ical_shifts, $user;
  
  if (isset($_REQUEST['key']) && preg_match("/^[0-9a-f]{32}$/", $_REQUEST['key']))
    $key = $_REQUEST['key'];
  else
    die("Missing key.");
  
  $user = User_by_api_key($key);
  if ($user === false)
    die("Unable to find user.");
  if ($user == null)
    die("Key invalid.");
  if (! in_array('shifts_json_export', privileges_for_user($user['UID'])))
    die("No privilege for shifts_json_export.");
  
  if (isset($_REQUEST['export']) && $_REQUEST['export'] == 'user_shifts') {
    require_once ('includes/pages/user_shifts.php');
    view_user_shifts();
  } else {
    $ical_shifts = sql_select("SELECT `Shifts`.*, `Room`.`Name` as `room_name` FROM `ShiftEntry` INNER JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`) INNER JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `UID`=" . sql_escape($user['UID']) . " ORDER BY `start`");
  }
  
  header("Content-Type: application/json; charset=utf-8");
  echo json_encode($ical_shifts);
  die();
}

?>