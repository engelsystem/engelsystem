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

/**
 * Edit a single shift.
 */
function shift_edit_controller() {
  global $privileges;
  
  // Schicht bearbeiten
  $msg = "";
  $valid = true;
  
  if (! in_array('admin_shifts', $privileges)) {
    redirect(page_link_to('user_shifts'));
  }
  
  if (! isset($_REQUEST['edit_shift']) || ! test_request_int('edit_shift')) {
    redirect(page_link_to('user_shifts'));
  }
  $shift_id = $_REQUEST['edit_shift'];
  
  // Locations laden
  $rooms = sql_select("SELECT * FROM `Room` WHERE `show`='Y' ORDER BY `Name`");
  $room_array = [];
  foreach ($rooms as $room) {
    $room_array[$room['RID']] = $room['Name'];
  }
  
  $shift = sql_select("
        SELECT `ShiftTypes`.`name`, `Shifts`.*, `Room`.* FROM `Shifts`
        JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`)
        JOIN `ShiftTypes` ON (`ShiftTypes`.`id` = `Shifts`.`shifttype_id`)
        WHERE `SID`='" . sql_escape($shift_id) . "'");
  if (count($shift) == 0) {
    redirect(page_link_to('user_shifts'));
  }
  $shift = $shift[0];
  
  // Engeltypen laden
  $types = sql_select("SELECT * FROM `AngelTypes` ORDER BY `name`");
  $angel_types = [];
  $needed_angel_types = [];
  foreach ($types as $type) {
    $angel_types[$type['id']] = $type;
    $needed_angel_types[$type['id']] = 0;
  }
  
  $shifttypes_source = ShiftTypes();
  $shifttypes = [];
  foreach ($shifttypes_source as $shifttype) {
    $shifttypes[$shifttype['id']] = $shifttype['name'];
  }
  
  // Benötigte Engeltypen vom Raum
  $needed_angel_types_source = sql_select("SELECT `AngelTypes`.*, `NeededAngelTypes`.`count` FROM `AngelTypes` LEFT JOIN `NeededAngelTypes` ON (`NeededAngelTypes`.`angel_type_id` = `AngelTypes`.`id` AND `NeededAngelTypes`.`room_id`='" . sql_escape($shift['RID']) . "') ORDER BY `AngelTypes`.`name`");
  foreach ($needed_angel_types_source as $type) {
    if ($type['count'] != "") {
      $needed_angel_types[$type['id']] = $type['count'];
    }
  }
  
  // Benötigte Engeltypen von der Schicht
  $needed_angel_types_source = sql_select("SELECT `AngelTypes`.*, `NeededAngelTypes`.`count` FROM `AngelTypes` LEFT JOIN `NeededAngelTypes` ON (`NeededAngelTypes`.`angel_type_id` = `AngelTypes`.`id` AND `NeededAngelTypes`.`shift_id`='" . sql_escape($shift_id) . "') ORDER BY `AngelTypes`.`name`");
  foreach ($needed_angel_types_source as $type) {
    if ($type['count'] != "") {
      $needed_angel_types[$type['id']] = $type['count'];
    }
  }
  
  $shifttype_id = $shift['shifttype_id'];
  $title = $shift['title'];
  $rid = $shift['RID'];
  $start = $shift['start'];
  $end = $shift['end'];
  
  if (isset($_REQUEST['submit'])) {
    // Name/Bezeichnung der Schicht, darf leer sein
    $title = strip_request_item('title');
    
    // Auswahl der sichtbaren Locations für die Schichten
    if (isset($_REQUEST['rid']) && preg_match("/^[0-9]+$/", $_REQUEST['rid']) && isset($room_array[$_REQUEST['rid']])) {
      $rid = $_REQUEST['rid'];
    } else {
      $valid = false;
      $rid = $rooms[0]['RID'];
      $msg .= error(_("Please select a room."), true);
    }
    
    if (isset($_REQUEST['shifttype_id']) && isset($shifttypes[$_REQUEST['shifttype_id']])) {
      $shifttype_id = $_REQUEST['shifttype_id'];
    } else {
      $valid = false;
      $msg .= error(_('Please select a shifttype.'), true);
    }
    
    if (isset($_REQUEST['start']) && $tmp = DateTime::createFromFormat("Y-m-d H:i", trim($_REQUEST['start']))) {
      $start = $tmp->getTimestamp();
    } else {
      $valid = false;
      $msg .= error(_("Please enter a valid starting time for the shifts."), true);
    }
    
    if (isset($_REQUEST['end']) && $tmp = DateTime::createFromFormat("Y-m-d H:i", trim($_REQUEST['end']))) {
      $end = $tmp->getTimestamp();
    } else {
      $valid = false;
      $msg .= error(_("Please enter a valid ending time for the shifts."), true);
    }
    
    if ($start >= $end) {
      $valid = false;
      $msg .= error(_("The ending time has to be after the starting time."), true);
    }
    
    foreach ($needed_angel_types_source as $type) {
      if (isset($_REQUEST['type_' . $type['id']]) && preg_match("/^[0-9]+$/", trim($_REQUEST['type_' . $type['id']]))) {
        $needed_angel_types[$type['id']] = trim($_REQUEST['type_' . $type['id']]);
      } else {
        $valid = false;
        $msg .= error(sprintf(_("Please check your input for needed angels of type %s."), $type['name']), true);
      }
    }
    
    if ($valid) {
      $shift['shifttype_id'] = $shifttype_id;
      $shift['title'] = $title;
      $shift['RID'] = $rid;
      $shift['start'] = $start;
      $shift['end'] = $end;
      
      $result = Shift_update($shift);
      if ($result === false) {
        engelsystem_error('Unable to update shift.');
      }
      sql_query("DELETE FROM `NeededAngelTypes` WHERE `shift_id`='" . sql_escape($shift_id) . "'");
      $needed_angel_types_info = [];
      foreach ($needed_angel_types as $type_id => $count) {
        sql_query("INSERT INTO `NeededAngelTypes` SET `shift_id`='" . sql_escape($shift_id) . "', `angel_type_id`='" . sql_escape($type_id) . "', `count`='" . sql_escape($count) . "'");
        $needed_angel_types_info[] = $angel_types[$type_id]['name'] . ": " . $count;
      }
      
      engelsystem_log("Updated shift '" . $shifttypes[$shifttype_id] . ", " . $title . "' from " . date("Y-m-d H:i", $start) . " to " . date("Y-m-d H:i", $end) . " with angel types " . join(", ", $needed_angel_types_info));
      success(_("Shift updated."));
      
      redirect(shift_link([
          'SID' => $shift_id 
      ]));
    }
  }
  
  $angel_types = "";
  foreach ($types as $type) {
    $angel_types .= form_spinner('type_' . $type['id'], $type['name'], $needed_angel_types[$type['id']]);
  }
  
  return page_with_title(shifts_title(), [
      msg(),
      '<noscript>' . info(_("This page is much more comfortable with javascript."), true) . '</noscript>',
      form([
          form_select('shifttype_id', _('Shifttype'), $shifttypes, $shifttype_id),
          form_text('title', _("Title"), $title),
          form_select('rid', _("Room:"), $room_array, $rid),
          form_text('start', _("Start:"), date("Y-m-d H:i", $start)),
          form_text('end', _("End:"), date("Y-m-d H:i", $end)),
          '<h2>' . _("Needed angels") . '</h2>',
          $angel_types,
          form_submit('submit', _("Save")) 
      ]) 
  ]);
}

function shift_delete_controller() {
  global $privileges;
  
  if (! in_array('user_shifts_admin', $privileges)) {
    redirect(page_link_to('user_shifts'));
  }
  
  // Schicht komplett löschen (nur für admins/user mit user_shifts_admin privileg)
  if (! isset($_REQUEST['delete_shift']) || ! preg_match("/^[0-9]*$/", $_REQUEST['delete_shift'])) {
    redirect(page_link_to('user_shifts'));
  }
  $shift_id = $_REQUEST['delete_shift'];
  
  $shift = Shift($shift_id);
  if ($shift === false) {
    engelsystem_error('Unable to load shift.');
  }
  if ($shift == null) {
    redirect(page_link_to('user_shifts'));
  }
  
  // Schicht löschen bestätigt
  if (isset($_REQUEST['delete'])) {
    $result = Shift_delete($shift_id);
    if ($result === false) {
      engelsystem_error('Unable to delete shift.');
    }
    
    engelsystem_log("Deleted shift " . $shift['name'] . " from " . date("Y-m-d H:i", $shift['start']) . " to " . date("Y-m-d H:i", $shift['end']));
    success(_("Shift deleted."));
    redirect(page_link_to('user_shifts'));
  }
  
  return page_with_title(shifts_title(), [
      error(sprintf(_("Do you want to delete the shift %s from %s to %s?"), $shift['name'], date("Y-m-d H:i", $shift['start']), date("H:i", $shift['end'])), true),
      '<a class="button" href="?p=user_shifts&delete_shift=' . $shift_id . '&delete">' . _("delete") . '</a>' 
  ]);
}

function shift_controller() {
  global $user, $privileges;
  
  if (! in_array('user_shifts', $privileges)) {
    redirect(page_link_to('?'));
  }
  
  if (! isset($_REQUEST['shift_id'])) {
    redirect(page_link_to('user_shifts'));
  }
  
  $shift = Shift($_REQUEST['shift_id']);
  if ($shift === false) {
    engelsystem_error('Unable to load shift.');
  }
  if ($shift == null) {
    error(_('Shift could not be found.'));
    redirect(page_link_to('user_shifts'));
  }
  
  $shifttype = ShiftType($shift['shifttype_id']);
  if ($shifttype === false || $shifttype == null) {
    engelsystem_error('Unable to load shift type.');
  }
  
  $room = Room($shift['RID']);
  if ($room === false || $room == null) {
    engelsystem_error('Unable to load room.');
  }
  
  $angeltypes = AngelTypes();
  if ($angeltypes === false) {
    engelsystem_error('Unable to load angeltypes.');
  }
  
  $user_shifts = Shifts_by_user($user);
  if ($user_shifts === false) {
    engelsystem_error('Unable to load users shifts.');
  }
  
  $signed_up = false;
  foreach ($user_shifts as $user_shift) {
    if ($user_shift['SID'] == $shift['SID']) {
      $signed_up = true;
      break;
    }
  }
  
  return [
      $shift['name'],
      Shift_view($shift, $shifttype, $room, in_array('admin_shifts', $privileges), $angeltypes, in_array('user_shifts_admin', $privileges), in_array('admin_rooms', $privileges), in_array('shifttypes', $privileges), $user_shifts, $signed_up) 
  ];
}

function shifts_controller() {
  if (! isset($_REQUEST['action'])) {
    redirect(page_link_to('user_shifts'));
  }
  
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
  
  if (! in_array('user_shifts', $privileges)) {
    redirect(page_link_to('?'));
  }
  
  $upcoming_shifts = ShiftEntries_upcoming_for_user($user);
  if ($upcoming_shifts === false) {
    return false;
  }
  
  if (count($upcoming_shifts) > 0) {
    redirect(shift_link($upcoming_shifts[0]));
  }
  
  redirect(page_link_to('user_shifts'));
}

/**
 * Export all shifts using api-key.
 */
function shifts_json_export_all_controller() {
  global $api_key;
  
  if ($api_key == "") {
    engelsystem_error("Config contains empty apikey.");
  }
  
  if (! isset($_REQUEST['api_key'])) {
    engelsystem_error("Missing parameter api_key.");
  }
  
  if ($_REQUEST['api_key'] != $api_key) {
    engelsystem_error("Invalid api_key.");
  }
  
  $shifts_source = Shifts();
  if ($shifts_source === false) {
    engelsystem_error("Unable to load shifts.");
  }
  
  header("Content-Type: application/json; charset=utf-8");
  raw_output(json_encode($shifts_source));
}

/**
 * Export filtered shifts via JSON.
 * (Like iCal Export or shifts view)
 */
function shifts_json_export_controller() {
  global $ical_shifts, $user;
  
  if (! isset($_REQUEST['key']) || ! preg_match("/^[0-9a-f]{32}$/", $_REQUEST['key'])) {
    engelsystem_error("Missing key.");
  }
  
  $key = $_REQUEST['key'];
  
  $user = User_by_api_key($key);
  if ($user === false) {
    engelsystem_error("Unable to find user.");
  }
  if ($user == null) {
    engelsystem_error("Key invalid.");
  }
  if (! in_array('shifts_json_export', privileges_for_user($user['UID']))) {
    engelsystem_error("No privilege for shifts_json_export.");
  }
  
  $ical_shifts = load_ical_shifts();
  
  header("Content-Type: application/json; charset=utf-8");
  raw_output(json_encode($ical_shifts));
}

/**
 * Returns shifts to export.
 * Users shifts or user_shifts filter based shifts if export=user_shifts is given as param.
 */
function load_ical_shifts() {
  global $user, $ical_shifts;
  
  if (isset($_REQUEST['export']) && $_REQUEST['export'] == 'user_shifts') {
    require_once realpath(__DIR__ . '/user_shifts.php');
    view_user_shifts();
    
    return $ical_shifts;
  }
  
  return Shifts_by_user($user);
}

?>