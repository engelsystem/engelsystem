<?php

use Engelsystem\ShiftSignupState;
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
  
  $shift = Shift($shift_id);
  
  $room = select_array(Rooms(), 'RID', 'Name');
  $angeltypes = select_array(AngelTypes(), 'id', 'name');
  $shifttypes = select_array(ShiftTypes(), 'id', 'name');
  
  $needed_angel_types = select_array(NeededAngelTypes_by_shift($shift_id), 'id', 'count');
  foreach (array_keys($angeltypes) as $angeltype_id) {
    if (! isset($needed_angel_types[$angeltype_id])) {
      $needed_angel_types[$angeltype_id] = 0;
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
    if (isset($_REQUEST['rid']) && preg_match("/^[0-9]+$/", $_REQUEST['rid']) && isset($room[$_REQUEST['rid']])) {
      $rid = $_REQUEST['rid'];
    } else {
      $valid = false;
      $msg .= error(_("Please select a room."), true);
    }
    
    if (isset($_REQUEST['shifttype_id']) && isset($shifttypes[$_REQUEST['shifttype_id']])) {
      $shifttype_id = $_REQUEST['shifttype_id'];
    } else {
      $valid = false;
      $msg .= error(_('Please select a shifttype.'), true);
    }
    
    if (isset($_REQUEST['start']) && $tmp = parse_date("Y-m-d H:i", $_REQUEST['start'])) {
      $start = $tmp;
    } else {
      $valid = false;
      $msg .= error(_("Please enter a valid starting time for the shifts."), true);
    }
    
    if (isset($_REQUEST['end']) && $tmp = parse_date("Y-m-d H:i", $_REQUEST['end'])) {
      $end = $tmp;
    } else {
      $valid = false;
      $msg .= error(_("Please enter a valid ending time for the shifts."), true);
    }
    
    if ($start >= $end) {
      $valid = false;
      $msg .= error(_("The ending time has to be after the starting time."), true);
    }
    
    foreach ($needed_angel_types as $needed_angeltype_id => $needed_angeltype_name) {
      if (isset($_REQUEST['type_' . $needed_angeltype_id]) && test_request_int('type_' . $needed_angeltype_id)) {
        $needed_angel_types[$needed_angeltype_id] = trim($_REQUEST['type_' . $needed_angeltype_id]);
      } else {
        $valid = false;
        $msg .= error(sprintf(_("Please check your input for needed angels of type %s."), $needed_angeltype_name), true);
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
      NeededAngelTypes_delete_by_shift($shift_id);
      $needed_angel_types_info = [];
      foreach ($needed_angel_types as $type_id => $count) {
        NeededAngelType_add($shift_id, $type_id, null, $count);
        $needed_angel_types_info[] = $angeltypes[$type_id] . ": " . $count;
      }
      
      engelsystem_log("Updated shift '" . $shifttypes[$shifttype_id] . ", " . $title . "' from " . date("Y-m-d H:i", $start) . " to " . date("Y-m-d H:i", $end) . " with angel types " . join(", ", $needed_angel_types_info));
      success(_("Shift updated."));
      
      redirect(shift_link([
          'SID' => $shift_id 
      ]));
    }
  }
  
  $angel_types_spinner = "";
  foreach ($angeltypes as $angeltype_id => $angeltype_name) {
    $angel_types_spinner .= form_spinner('type_' . $angeltype_id, $angeltype_name, $needed_angel_types[$angeltype_id]);
  }
  
  return page_with_title(shifts_title(), [
      msg(),
      '<noscript>' . info(_("This page is much more comfortable with javascript."), true) . '</noscript>',
      form([
          form_select('shifttype_id', _('Shifttype'), $shifttypes, $shifttype_id),
          form_text('title', _("Title"), $title),
          form_select('rid', _("Room:"), $room, $rid),
          form_text('start', _("Start:"), date("Y-m-d H:i", $start)),
          form_text('end', _("End:"), date("Y-m-d H:i", $end)),
          '<h2>' . _("Needed angels") . '</h2>',
          $angel_types_spinner,
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
  if ($shift == null) {
    redirect(page_link_to('user_shifts'));
  }
  
  // Schicht löschen bestätigt
  if (isset($_REQUEST['delete'])) {
    Shift_delete($shift_id);
    
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
  if ($shift == null) {
    error(_("Shift could not be found."));
    redirect(page_link_to('user_shifts'));
  }
  
  $shifttype = ShiftType($shift['shifttype_id']);
  $room = Room($shift['RID']);
  $angeltypes = AngelTypes();
  $user_shifts = Shifts_by_user($user);
  
  $shift_signup_state = new ShiftSignupState(ShiftSignupState::OCCUPIED, 0);
  foreach ($angeltypes as $angeltype) {
    $angeltype_signup_state = Shift_signup_allowed($user, $shift, $angeltype, null, $user_shifts);
    if ($shift_signup_state == null) {
      $shift_signup_state = $angeltype_signup_state;
    } else {
      $shift_signup_state->combineWith($angeltype_signup_state);
    }
  }
  
  return [
      $shift['name'],
      Shift_view($shift, $shifttype, $room, $angeltypes, $shift_signup_state) 
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