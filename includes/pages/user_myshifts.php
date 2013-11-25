<?php
function myshifts_title() {
  return _("My shifts");
}

// Zeigt die Schichten an, die ein Benutzer belegt
function user_myshifts() {
  global $LETZTES_AUSTRAGEN;
  global $user, $privileges;
  $msg = "";
  
  if (isset($_REQUEST['id']) && in_array("user_shifts_admin", $privileges) && preg_match("/^[0-9]{1,}$/", $_REQUEST['id']) && sql_num_query("SELECT * FROM `User` WHERE `UID`=" . sql_escape($_REQUEST['id'])) > 0) {
    $id = $_REQUEST['id'];
  } else {
    $id = $user['UID'];
  }
  
  list($shifts_user) = sql_select("SELECT * FROM `User` WHERE `UID`=" . sql_escape($id) . " LIMIT 1");
  
  if (isset($_REQUEST['reset'])) {
    if ($_REQUEST['reset'] == "ack") {
      User_reset_api_key($user);
      success("Key geändert.");
      redirect(page_link_to('user_myshifts'));
    }
    return template_render('../templates/user_myshifts_reset.html', array());
  } elseif (isset($_REQUEST['edit']) && preg_match("/^[0-9]*$/", $_REQUEST['edit'])) {
    $id = $_REQUEST['edit'];
    $shift = sql_select("SELECT `ShiftEntry`.`Comment`, `ShiftEntry`.`UID`, `Shifts`.*, `Room`.`Name`, `AngelTypes`.`name` as `angel_type` FROM `ShiftEntry` JOIN `AngelTypes` ON (`ShiftEntry`.`TID` = `AngelTypes`.`id`) JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`) JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `ShiftEntry`.`id`=" . sql_escape($id) . " AND `UID`=" . sql_escape($shifts_user['UID']) . " LIMIT 1");
    if (count($shift) > 0) {
      $shift = $shift[0];
      
      if (isset($_REQUEST['submit'])) {
        $comment = strip_request_item_nl('comment');
        $user_source = User($shift['UID']);
        sql_query("UPDATE `ShiftEntry` SET `Comment`='" . sql_escape($comment) . "' WHERE `id`=" . sql_escape($id) . " LIMIT 1");
        engelsystem_log("Updated " . User_Nick_render($user_source) . "'s shift " . $shift['name'] . " from " . date("y-m-d H:i", $shift['start']) . " to " . date("y-m-d H:i", $shift['end']) . " with comment " . $comment);
        success("Schicht gespeichert.");
        redirect(page_link_to('user_myshifts'));
      }
      
      return ShiftEntry_edit_view(User_Nick_render($shifts_user), date("Y-m-d H:i", $shift['start']) . ', ' . shift_length($shift), $shift['Name'], $shift['name'], $shift['angel_type'], $shift['Comment']);
    } else
      redirect(page_link_to('user_myshifts'));
  } elseif (isset($_REQUEST['cancel']) && preg_match("/^[0-9]*$/", $_REQUEST['cancel'])) {
    $id = $_REQUEST['cancel'];
    $shift = sql_select("SELECT `Shifts`.`start` FROM `Shifts` INNER JOIN `ShiftEntry` USING (`SID`) WHERE `ShiftEntry`.`id`=" . sql_escape($id) . " AND `UID`=" . sql_escape($shifts_user['UID']) . " LIMIT 1");
    if (count($shift) > 0) {
      $shift = $shift[0];
      if (($shift['start'] > time() + $LETZTES_AUSTRAGEN * 3600) || in_array('user_shifts_admin', $privileges)) {
        sql_query("DELETE FROM `ShiftEntry` WHERE `id`=" . sql_escape($id) . " LIMIT 1");
        $msg .= success(_("You have been signed off from the shift."), true);
      } else
        $msg .= error(_("It's too late to sign yourself off the shift. If neccessary, as the dispatcher to do so."), true);
    } else
      redirect(page_link_to('user_myshifts'));
  }
  $shifts = sql_select("SELECT * FROM `ShiftEntry` JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`) JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `UID`=" . sql_escape($shifts_user['UID']) . " ORDER BY `start`");
  
  $myshifts_table = array();
  $html = "";
  $timesum = 0;
  foreach ($shifts as $shift) {
    $shift_info = $shift['name'];
    $needed_angel_types_source = sql_select("SELECT DISTINCT `AngelTypes`.* FROM `ShiftEntry` JOIN `AngelTypes` ON `ShiftEntry`.`TID`=`AngelTypes`.`id` WHERE `ShiftEntry`.`SID`=" . sql_escape($shift['SID']) . "  ORDER BY `AngelTypes`.`name`");
    foreach ($needed_angel_types_source as $needed_angel_type) {
      $shift_info .= '<br><b>' . $needed_angel_type['name'] . ':</b> ';
      
      $users_source = sql_select("SELECT `User`.* FROM `ShiftEntry` JOIN `User` ON `ShiftEntry`.`UID`=`User`.`UID` WHERE `ShiftEntry`.`SID`=" . sql_escape($shift['SID']) . " AND `ShiftEntry`.`TID`=" . sql_escape($needed_angel_type['id']));
      $shift_entries = array();
      foreach ($users_source as $user_source) {
        if ($user['UID'] == $user_source['UID'])
          $shift_entries[] = '<b>' . $user_source['Nick'] . '</b>';
        else
          $shift_entries[] = User_Nick_render($user_source);
      }
      $shift_info .= join(", ", $shift_entries);
    }
    
    $myshift = array(
        'date' => date("Y-m-d", $shift['start']),
        'time' => date("H:i", $shift['start']) . ' - ' . date("H:i", $shift['end']),
        'room' => $shift['Name'],
        'shift_info' => $shift_info,
        'comment' => $shift['Comment'] 
    );
    
    $myshift['actions'] = "";
    if ($id == $user['UID'])
      $myshift['actions'] .= img_button(page_link_to('user_myshifts') . '&edit=' . $shift['id'], 'pencil', _("edit"));
    if (($shift['start'] > time() + $LETZTES_AUSTRAGEN * 3600) || in_array('user_shifts_admin', $privileges))
      $myshift['actions'] .= img_button(page_link_to('user_myshifts') . (($id != $user['UID']) ? '&id=' . $id : '') . '&cancel=' . $shift['id'], 'cross', _("sign off"));
    
    $timesum += $shift['end'] - $shift['start'];
    $myshifts_table[] = $myshift;
  }
  if (count($myshifts_table) > 0)
    $myshifts_table[] = array(
        'date' => '<b>' . _("Sum:") . '</b>',
        'time' => "<b>" . round($timesum / (60 * 60), 1) . " h</b>",
        'room' => "",
        'shift_info' => "",
        'comment' => "",
        'actions' => "" 
    );
  
  return page(array(
      msg(),
      $id == $user['UID'] ? sprintf(_('These are your shifts.<br/>Please try to appear <b>15 minutes</b> before your shift begins!<br/>You can remove yourself from a shift up to %d hours before it starts.'), $LETZTES_AUSTRAGEN) : '',
      $id != $user['UID'] ? info(sprintf("You are viewing %s's shifts.", $shifts_user['Nick']), true) : '',
      $id != $user['UID'] ? buttons(array(
          button(page_link_to('admin_user') . '&amp;id=' . $shifts_user['UID'], "Edit " . $shifts_user['Nick'], 'edit') 
      )) : '',
      table(array(
          'date' => _("Day"),
          'time' => _("Time"),
          'room' => _("Location"),
          'shift_info' => _("Name &amp; workmates"),
          'comment' => _("Comment"),
          'actions' => _("Action") 
      ), $myshifts_table),
      $id == $user['UID'] && count($shifts) == 0 ? error(sprintf(_("Go to the <a href=\"%s\">shifts table</a> to sign yourself up for some shifts."), page_link_to('user_shifts')), true) : '',
      '<h2>' . _("Exports") . '</h2>' . sprintf(_("Export of shown shifts. <a href=\"%s\">iCal format</a> or <a href=\"%s\">JSON format</a> available (please keep secret, otherwise <a href=\"%s\">reset the api key</a>)."), page_link_to_absolute('ical') . '&key=' . $shifts_user['api_key'], page_link_to_absolute('shifts_json_export') . '&key=' . $shifts_user['api_key'], page_link_to('user_myshifts') . '&reset') 
  ));
}
?>
