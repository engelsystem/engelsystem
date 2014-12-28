<?php

function shift_link($shift) {
  return page_link_to('shifts') . '&action=view&shift_id=' . $shift['SID'];
}

function shift_delete_link($shift) {
  return page_link_to('user_shifts') . '&delete_shift=' . $shift['SID'];
}

function shift_edit_link($shift) {
  return page_link_to('user_shifts') . '&edit_shift=' . $shift['SID'];
}

function shift_controller() {
  global $user, $privileges;
  
  if (! in_array('user_shifts', $privileges))
    redirect(page_link_to('?'));
  
  if (! isset($_REQUEST['shift_id']))
    redirect(page_link_to('user_shifts'));
  
  $shift = Shift($_REQUEST['shift_id']);
  if ($shift === false)
    engelsystem_error('Unable to load shift.');
  if ($shift == null) {
    error(_('Shift could not be found.'));
    redirect(page_link_to('user_shifts'));
  }
  
  $shifttype = ShiftType($shift['shifttype_id']);
  if ($shifttype === false || $shifttype == null)
    engelsystem_error('Unable to load shift type.');
  
  $room = Room($shift['RID']);
  if ($room === false || $room == null)
    engelsystem_error('Unable to load room.');
  
  $angeltypes = AngelTypes();
  if ($angeltypes === false)
    engelsystem_error('Unable to load angeltypes.');
  
  $user_shifts = Shifts_by_user($user);
  if ($user_shifts === false)
    engelsystem_error('Unable to load users shifts.');
  
  $signed_up = false;
  foreach ($user_shifts as $user_shift)
    if ($user_shift['SID'] == $shift['SID']) {
      $signed_up = true;
      break;
    }
  
  return [
      $shift['name'],
      Shift_view($shift, $shifttype, $room, in_array('admin_shifts', $privileges), $angeltypes, in_array('user_shifts_admin', $privileges), in_array('admin_rooms', $privileges), in_array('shifttypes', $privileges), $user_shifts, $signed_up) 
  ];
}

function shifts_controller() {
  if (! isset($_REQUEST['action']))
    redirect(page_link_to('user_shifts'));
  
  switch ($_REQUEST['action']) {
    default:
      redirect(page_link_to('?'));
    case 'view':
      return shift_controller();
    case 'next':
      return shift_next_controller();
  }
}

/**
 * Redirects the user to his next shift.
 */
function shift_next_controller() {
  global $user, $privileges;
  
  if (! in_array('user_shifts', $privileges))
    redirect(page_link_to('?'));
  
  $upcoming_shifts = ShiftEntries_upcoming_for_user($user);
  if ($upcoming_shifts === false)
    return false;
  
  if (count($upcoming_shifts) > 0)
    redirect(shift_link($upcoming_shifts[0]));
  
  redirect(page_link_to('user_shifts'));
}

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
    require_once realpath(__DIR__ . '/../pages/user_shifts.php');
    view_user_shifts();
  } else {
    $ical_shifts = sql_select("
        SELECT `ShiftTypes`.`name`, `Shifts`.*, `Room`.`Name` as `room_name` 
        FROM `ShiftEntry` 
        INNER JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`) 
        INNER JOIN `ShiftTypes` ON (`Shifts`.`shifttype_id`=`ShiftTypes`.`id`)
        INNER JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) 
        WHERE `UID`='" . sql_escape($user['UID']) . "' 
        ORDER BY `start`");
  }
  
  header("Content-Type: application/json; charset=utf-8");
  echo json_encode($ical_shifts);
  die();
}

?>