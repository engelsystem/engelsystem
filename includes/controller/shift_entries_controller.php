<?php

/**
 * Sign up for a shift.
 */
function shift_entry_add_controller() {
  global $privileges, $user;
  
  if (isset($_REQUEST['shift_id']) && preg_match("/^[0-9]*$/", $_REQUEST['shift_id'])) {
    $shift_id = $_REQUEST['shift_id'];
  } else {
    redirect(page_link_to('user_shifts'));
  }
  
  // Locations laden
  $rooms = sql_select("SELECT * FROM `Room` WHERE `show`='Y' ORDER BY `Name`");
  $room_array = [];
  foreach ($rooms as $room) {
    $room_array[$room['RID']] = $room['Name'];
  }
  
  $shift = Shift($shift_id);
  $shift['Name'] = $room_array[$shift['RID']];
  if ($shift == null) {
    redirect(page_link_to('user_shifts'));
  }
  
  if (isset($_REQUEST['type_id']) && preg_match("/^[0-9]*$/", $_REQUEST['type_id'])) {
    $type_id = $_REQUEST['type_id'];
  } else {
    redirect(page_link_to('user_shifts'));
  }
  
  if (in_array('user_shifts_admin', $privileges)) {
    $type = sql_select("SELECT * FROM `AngelTypes` WHERE `id`='" . sql_escape($type_id) . "' LIMIT 1");
  } else {
    $type = sql_select("SELECT * FROM `UserAngelTypes` JOIN `AngelTypes` ON (`UserAngelTypes`.`angeltype_id` = `AngelTypes`.`id`) WHERE `AngelTypes`.`id` = '" . sql_escape($type_id) . "' AND (`AngelTypes`.`restricted` = 0 OR (`UserAngelTypes`.`user_id` = '" . sql_escape($user['UID']) . "' AND NOT `UserAngelTypes`.`confirm_user_id` IS NULL)) LIMIT 1");
  }


  if (count($type) == 0) {
    redirect(page_link_to('user_shifts'));
  }
  $type = $type[0];

  if (isset($_REQUEST['user_id']) && preg_match("/^[0-9]*$/", $_REQUEST['user_id']) &&
      in_array('user_shifts_admin', $privileges)) {
    $user_id = $_REQUEST['user_id'];
  } else {
    $user_id = $user['UID'];
  }

  $shift_signup_allowed = Shift_signup_allowed(User($user_id), $shift, $type);
  if (! $shift_signup_allowed->isSignupAllowed()) {
    error(_("You are not allowed to sign up for this shift. Maybe shift is full or already running."));
    redirect(shift_link($shift));
  }
  
  if (isset($_REQUEST['submit'])) {
    $selected_type_id = $type_id;
    if (in_array('user_shifts_admin', $privileges)) {

      if (sql_num_query("SELECT * FROM `User` WHERE `UID`='" . sql_escape($user_id) . "' LIMIT 1") == 0) {
        redirect(page_link_to('user_shifts'));
      }
      
      if (isset($_REQUEST['angeltype_id']) && test_request_int('angeltype_id') && sql_num_query("SELECT * FROM `AngelTypes` WHERE `id`='" . sql_escape($_REQUEST['angeltype_id']) . "' LIMIT 1") > 0) {
        $selected_type_id = $_REQUEST['angeltype_id'];
      }
    }
    
    if (sql_num_query("SELECT * FROM `ShiftEntry` WHERE `SID`='" . sql_escape($shift['SID']) . "' AND `UID` = '" . sql_escape($user_id) . "'")) {
      return error("This angel does already have an entry for this shift.", true);
    }
    
    $freeloaded = $shift['freeloaded'];
    $freeload_comment = $shift['freeload_comment'];
    if (in_array("user_shifts_admin", $privileges)) {
      $freeloaded = isset($_REQUEST['freeloaded']);
      $freeload_comment = strip_request_item_nl('freeload_comment');
    }
    
    $comment = strip_request_item_nl('comment');
    $result = ShiftEntry_create([
        'SID' => $shift_id,
        'TID' => $selected_type_id,
        'UID' => $user_id,
        'Comment' => $comment,
        'freeloaded' => $freeloaded,
        'freeload_comment' => $freeload_comment 
    ]);
    if ($result === false) {
      engelsystem_error('Unable to create shift entry.');
    }
    
    if ($type['restricted'] == 0 && sql_num_query("SELECT * FROM `UserAngelTypes` INNER JOIN `AngelTypes` ON `AngelTypes`.`id` = `UserAngelTypes`.`angeltype_id` WHERE `angeltype_id` = '" . sql_escape($selected_type_id) . "' AND `user_id` = '" . sql_escape($user_id) . "' ") == 0) {
      sql_query("INSERT INTO `UserAngelTypes` (`user_id`, `angeltype_id`) VALUES ('" . sql_escape($user_id) . "', '" . sql_escape($selected_type_id) . "')");
    }
    
    $user_source = User($user_id);
    engelsystem_log("User " . User_Nick_render($user_source) . " signed up for shift " . $shift['name'] . " from " . date("Y-m-d H:i", $shift['start']) . " to " . date("Y-m-d H:i", $shift['end']));
    success(_("You are subscribed. Thank you!") . ' <a href="' . page_link_to('user_myshifts') . '">' . _("My shifts") . ' &raquo;</a>');
    redirect(shift_link($shift));
  }
  
  if (in_array('user_shifts_admin', $privileges)) {
    $users = sql_select("SELECT *, (SELECT count(*) FROM `ShiftEntry` WHERE `freeloaded`=1 AND `ShiftEntry`.`UID`=`User`.`UID`) AS `freeloaded` FROM `User` ORDER BY `Nick`");
    $users_select = [];
    
    foreach ($users as $usr) {
      $users_select[$usr['UID']] = $usr['Nick'] . ($usr['freeloaded'] == 0 ? "" : " (" . _("Freeloader") . ")");
    }
    $user_text = html_select_key('user_id', 'user_id', $users_select, $user['UID']);
    
    $angeltypes_source = sql_select("SELECT * FROM `AngelTypes` ORDER BY `name`");
    $angeltypes = [];
    foreach ($angeltypes_source as $angeltype) {
      $angeltypes[$angeltype['id']] = $angeltype['name'];
    }
    $angeltype_select = html_select_key('angeltype_id', 'angeltype_id', $angeltypes, $type['id']);
  } else {
    $user_text = User_Nick_render($user);
    $angeltype_select = $type['name'];
  }
  
  return ShiftEntry_edit_view($user_text, date("Y-m-d H:i", $shift['start']) . ' &ndash; ' . date('Y-m-d H:i', $shift['end']) . ' (' . shift_length($shift) . ')', $shift['Name'], $shift['name'], $angeltype_select, "", false, null, in_array('user_shifts_admin', $privileges));
}

/**
 * Remove somebody from a shift.
 */
function shift_entry_delete_controller() {
  global $privileges;
  
  if (! in_array('user_shifts_admin', $privileges)) {
    redirect(page_link_to('user_shifts'));
  }
  
  if (! isset($_REQUEST['entry_id']) || ! test_request_int('entry_id')) {
    redirect(page_link_to('user_shifts'));
  }
  $entry_id = $_REQUEST['entry_id'];
  
  $shift_entry_source = sql_select("
        SELECT `User`.`Nick`, `ShiftEntry`.`Comment`, `ShiftEntry`.`UID`, `ShiftTypes`.`name`, `Shifts`.*, `Room`.`Name`, `AngelTypes`.`name` as `angel_type`
        FROM `ShiftEntry`
        JOIN `User` ON (`User`.`UID`=`ShiftEntry`.`UID`)
        JOIN `AngelTypes` ON (`ShiftEntry`.`TID` = `AngelTypes`.`id`)
        JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`)
        JOIN `ShiftTypes` ON (`ShiftTypes`.`id` = `Shifts`.`shifttype_id`)
        JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`)
        WHERE `ShiftEntry`.`id`='" . sql_escape($entry_id) . "'");
  if (count($shift_entry_source) > 0) {
    $shift_entry_source = $shift_entry_source[0];
    
    $result = ShiftEntry_delete($entry_id);
    if ($result === false) {
      engelsystem_error('Unable to delete shift entry.');
    }
    
    engelsystem_log("Deleted " . User_Nick_render($shift_entry_source) . "'s shift: " . $shift_entry_source['name'] . " at " . $shift_entry_source['Name'] . " from " . date("Y-m-d H:i", $shift_entry_source['start']) . " to " . date("Y-m-d H:i", $shift_entry_source['end']) . " as " . $shift_entry_source['angel_type']);
    success(_("Shift entry deleted."));
  } else {
    error(_("Entry not found."));
  }
  redirect(page_link_to('user_shifts'));
}

?>