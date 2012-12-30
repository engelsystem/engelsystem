<?php


// Zeigt die Schichten an, die ein Benutzer belegt
function user_myshifts() {
  global $LETZTES_AUSTRAGEN;
  global $user, $privileges;
  $msg = "";

  if (isset ($_REQUEST['id']) && in_array("user_shifts_admin", $privileges) && preg_match("/^[0-9]{1,}$/", $_REQUEST['id']) && sql_num_query("SELECT * FROM `User` WHERE `UID`=" . sql_escape($_REQUEST['id'])) > 0) {
    $id = $_REQUEST['id'];
  } else {
    $id = $user['UID'];
  }

  list ($shifts_user) = sql_select("SELECT * FROM `User` WHERE `UID`=" . sql_escape($id) . " LIMIT 1");

  if (isset ($_REQUEST['reset'])) {
    if ($_REQUEST['reset'] == "ack") {
      user_reset_ical_key($user);
      success("Key geändert.");
      redirect(page_link_to('user_myshifts'));
    }
    return template_render('../templates/user_myshifts_reset.html', array ());
  }
  elseif (isset ($_REQUEST['edit']) && preg_match("/^[0-9]*$/", $_REQUEST['edit'])) {
    $id = $_REQUEST['edit'];
    $shift = sql_select("SELECT `ShiftEntry`.`Comment`, `ShiftEntry`.`UID`, `Shifts`.*, `Room`.`Name`, `AngelTypes`.`name` as `angel_type` FROM `ShiftEntry` JOIN `AngelTypes` ON (`ShiftEntry`.`TID` = `AngelTypes`.`id`) JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`) JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `ShiftEntry`.`id`=" . sql_escape($id) . " AND `UID`=" . sql_escape($shifts_user['UID']) . " LIMIT 1");
    if (count($shift) > 0) {
      $shift = $shift[0];

      if (isset ($_REQUEST['submit'])) {
        $comment = strip_request_item_nl('comment');
        $user_source = User($shift['UID']);
        sql_query("UPDATE `ShiftEntry` SET `Comment`='" . sql_escape($comment) . "' WHERE `id`=" . sql_escape($id) . " LIMIT 1");
        engelsystem_log("Updated " . User_Nick_render($user_source) . "'s shift " . $shift['name'] . " from " . date("y-m-d H:i", $shift['start']) . " to " . date("y-m-d H:i", $shift['end']) . " with comment " . $comment);
        success("Schicht gespeichert.");
        redirect(page_link_to('user_myshifts'));
      }

      return template_render('../templates/user_shifts_add.html', array (
        'angel' => User_Nick_render($shifts_user),
        'date' => date("Y-m-d H:i", $shift['start']) . ', ' . shift_length($shift),
        'location' => $shift['Name'],
        'title' => $shift['name'],
        'type' => $shift['angel_type'],
        'comment' => $shift['Comment']
      ));
    } else
      redirect(page_link_to('user_myshifts'));
  }
  elseif (isset ($_REQUEST['cancel']) && preg_match("/^[0-9]*$/", $_REQUEST['cancel'])) {
    $id = $_REQUEST['cancel'];
    $shift = sql_select("SELECT `Shifts`.`start` FROM `Shifts` INNER JOIN `ShiftEntry` USING (`SID`) WHERE `ShiftEntry`.`id`=" . sql_escape($id) . " AND `UID`=" . sql_escape($shifts_user['UID']) . " LIMIT 1");
    if (count($shift) > 0) {
      $shift = $shift[0];
      if (($shift['start'] > time() + $LETZTES_AUSTRAGEN * 3600) || in_array('user_shifts_admin', $privileges)) {
        sql_query("DELETE FROM `ShiftEntry` WHERE `id`=" . sql_escape($id) . " LIMIT 1");
        $msg .= success(Get_Text("pub_myshifts_signed_off"), true);
      } else
        $msg .= error(Get_Text("pub_myshifts_too_late"), true);
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
    foreach($needed_angel_types_source as $needed_angel_type) {
      $shift_info .= '<br><b>' . $needed_angel_type['name'] . ':</b> ';

      $users_source = sql_select("SELECT `User`.* FROM `ShiftEntry` JOIN `User` ON `ShiftEntry`.`UID`=`User`.`UID` WHERE `ShiftEntry`.`SID`=" . sql_escape($shift['SID']) . " AND `ShiftEntry`.`TID`=" . sql_escape($needed_angel_type['id']));
      $shift_entries = array();
      foreach($users_source as $user_source) {
        if($user['UID'] == $user_source['UID'])
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
      $myshift['actions'] .= img_button(page_link_to('user_myshifts') . '&edit=' . $shift['id'], 'pencil', 'edit');
    if (($shift['start'] > time() + $LETZTES_AUSTRAGEN * 3600) || in_array('user_shifts_admin', $privileges))
      $myshift['actions'] .= img_button(page_link_to('user_myshifts') . (($id != $user['UID'])? '&id=' . $id : '') . '&cancel=' . $shift['id'], 'cross', 'sign_off');

    $timesum += $shift['end'] - $shift['start'];
    $myshifts_table[] = $myshift;
  }
  if (count($shifts) == 0)
    $html = '<tr><td>' . ucfirst(Get_Text('none')) . '...</td><td></td><td></td><td></td><td></td><td>' . sprintf(Get_Text('pub_myshifts_goto_shifts'), page_link_to('user_shifts')) . '</td></tr>';

  return page(array(
    msg(),
    $id == $user['UID'] ? sprintf(Get_Text('pub_myshifts_intro'), $LETZTES_AUSTRAGEN) : '',
    $id != $user['UID'] ? info(sprintf("You are viewing %s's shifts.", $shifts_user['Nick']), true) : '',
    $id != $user['UID'] ? buttons(array(button(page_link_to('admin_user') . '&amp;id=' . $shifts_user['UID'], "Edit " . $shifts_user['Nick'], 'edit'))) : '',
    table(array(
      'date' => "Tag",
      'time' => "Zeit",
      'room' => "Ort",
      'shift_info' => "Name &amp; Kollegen",
      'comment' => "Kommentar",
      'actions' => "Aktion"
    ), $myshifts_table),
    $id == $user['UID'] && count($shifts) == 0 ? error(sprintf(Get_Text('pub_myshifts_goto_shifts'), page_link_to('user_shifts')), true) : '',
    "<h2>iCal Export</h2>" . sprintf(Get_Text('inc_schicht_ical_text'), page_link_to_absolute('ical') . '&key=' . $shifts_user['ical_key'], page_link_to('user_myshifts') . '&reset')
  ));
}
?>
