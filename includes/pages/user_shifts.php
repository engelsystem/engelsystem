<?php
function user_shifts() {
  global $user, $privileges;

  // Löschen einzelner Schicht-Einträge (Also Belegung einer Schicht von Engeln) durch Admins
  if (isset ($_REQUEST['entry_id']) && in_array('user_shifts_admin', $privileges)) {
    if (isset ($_REQUEST['entry_id']) && test_request_int('entry_id'))
      $entry_id = $_REQUEST['entry_id'];
    else
      redirect(page_link_to('user_shifts'));

    $shift_entry_source = sql_select("SELECT `User`.`Nick`, `ShiftEntry`.`Comment`, `ShiftEntry`.`UID`, `Shifts`.*, `Room`.`Name`, `AngelTypes`.`name` as `angel_type` FROM `ShiftEntry` JOIN `User` ON (`User`.`UID`=`ShiftEntry`.`UID`) JOIN `AngelTypes` ON (`ShiftEntry`.`TID` = `AngelTypes`.`id`) JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`) JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `ShiftEntry`.`id`=" . sql_escape($entry_id) . " LIMIT 1");
    if(count($shift_entry_source)  > 0) {
      $shift_entry_source = $shift_entry_source[0];
      sql_query("DELETE FROM `ShiftEntry` WHERE `id`=" . sql_escape($entry_id) . " LIMIT 1");

      engelsystem_log("Deleted " . $shift_entry_source['Nick'] . "'s shift: " . $shift_entry_source['name'] . " at " . $shift_entry_source['Name'] . " from " . date("y-m-d H:i", $shift_entry_source['start']) . " to " . date("y-m-d H:i", $shift_entry_source['end']) . " as " . $shift_entry_source['angel_type']);
      success("Der Schicht-Eintrag wurde gelöscht.");
    }
    else error("Entry not found.");
    redirect(page_link_to('user_shifts'));
  }
  // Schicht bearbeiten
  elseif (isset ($_REQUEST['edit_shift']) && in_array('admin_shifts', $privileges)) {
    $msg = "";
    $ok = true;

    if (isset ($_REQUEST['edit_shift']) && test_request_int('edit_shift'))
      $shift_id = $_REQUEST['edit_shift'];
    else
      redirect(page_link_to('user_shifts'));

    /*
     if (sql_num_query("SELECT * FROM `ShiftEntry` WHERE `SID`=" . sql_escape($shift_id) . " LIMIT 1") > 0) {
    error("Du kannst nur Schichten bearbeiten, bei denen niemand eingetragen ist.");
    redirect(page_link_to('user_shift'));
    }
    */

    $shift = sql_select("SELECT * FROM `Shifts` JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `SID`=" . sql_escape($shift_id) . " LIMIT 1");
    if (count($shift) == 0)
      redirect(page_link_to('user_shifts'));
    $shift = $shift[0];

    // Locations laden
    $rooms = sql_select("SELECT * FROM `Room` WHERE `show`='Y' ORDER BY `Name`");
    $room_array = array ();
    foreach ($rooms as $room)
      $room_array[$room['RID']] = $room['Name'];

    // Engeltypen laden
    $types = sql_select("SELECT * FROM `AngelTypes` ORDER BY `name`");
    $angel_types = array();
    $needed_angel_types = array ();
    foreach ($types as $type) {
      $angel_types[$type['id']] = $type;
      $needed_angel_types[$type['id']] = 0;
    }

    // Benötigte Engeltypen vom Raum
    $needed_angel_types_source = sql_select("SELECT `AngelTypes`.*, `NeededAngelTypes`.`count` FROM `AngelTypes` LEFT JOIN `NeededAngelTypes` ON (`NeededAngelTypes`.`angel_type_id` = `AngelTypes`.`id` AND `NeededAngelTypes`.`room_id`=" . sql_escape($shift['RID']) . ") ORDER BY `AngelTypes`.`name`");
    foreach ($needed_angel_types_source as $type) {
      if($type['count'] != "")
        $needed_angel_types[$type['id']] =$type['count'];
    }

    // Benötigte Engeltypen von der Schicht
    $needed_angel_types_source = sql_select("SELECT `AngelTypes`.*, `NeededAngelTypes`.`count` FROM `AngelTypes` LEFT JOIN `NeededAngelTypes` ON (`NeededAngelTypes`.`angel_type_id` = `AngelTypes`.`id` AND `NeededAngelTypes`.`shift_id`=" . sql_escape($shift_id) . ") ORDER BY `AngelTypes`.`name`");
    foreach ($needed_angel_types_source as $type){
      if($type['count'] != "")
        $needed_angel_types[$type['id']] =$type['count'];
    }

    $name = $shift['name'];
    $rid = $shift['RID'];
    $start = $shift['start'];
    $end = $shift['end'];

    if (isset ($_REQUEST['submit'])) {
      // Name/Bezeichnung der Schicht, darf leer sein
      $name = strip_request_item('name');

      // Auswahl der sichtbaren Locations für die Schichten
      if (isset ($_REQUEST['rid']) && preg_match("/^[0-9]+$/", $_REQUEST['rid']) && isset ($room_array[$_REQUEST['rid']]))
        $rid = $_REQUEST['rid'];
      else {
        $ok = false;
        $rid = $rooms[0]['RID'];
        $msg .= error("Wähle bitte einen Raum aus.", true);
      }

      if (isset ($_REQUEST['start']) && $tmp = DateTime :: createFromFormat("Y-m-d H:i", trim($_REQUEST['start'])))
        $start = $tmp->getTimestamp();
      else {
        $ok = false;
        $msg .= error("Bitte gib einen Startzeitpunkt für die Schichten an.", true);
      }

      if (isset ($_REQUEST['end']) && $tmp = DateTime :: createFromFormat("Y-m-d H:i", trim($_REQUEST['end'])))
        $end = $tmp->getTimestamp();
      else {
        $ok = false;
        $msg .= error("Bitte gib einen Endzeitpunkt für die Schichten an.", true);
      }

      if ($start >= $end) {
        $ok = false;
        $msg .= error("Das Ende muss nach dem Startzeitpunkt liegen!", true);
      }

      foreach ($needed_angel_types_source as $type) {
        if (isset ($_REQUEST['type_' . $type['id']]) && preg_match("/^[0-9]+$/", trim($_REQUEST['type_' . $type['id']]))) {
          $needed_angel_types[$type['id']] = trim($_REQUEST['type_' . $type['id']]);
        } else {
          $ok = false;
          $msg .= error("Bitte überprüfe die Eingaben für die benötigten Engel des Typs " . $type['name'] . ".", true);
        }
      }

      if ($ok) {
        sql_query("UPDATE `Shifts` SET `start`=" . sql_escape($start) . ", `end`=" . sql_escape($end) . ", `RID`=" . sql_escape($rid) . ", `name`='" . sql_escape($name) . "' WHERE `SID`=" . sql_escape($shift_id) . " LIMIT 1");
        sql_query("DELETE FROM `NeededAngelTypes` WHERE `shift_id`=" . sql_escape($shift_id));
        $needed_angel_types_info = array();
        foreach ($needed_angel_types as $type_id => $count) {
          sql_query("INSERT INTO `NeededAngelTypes` SET `shift_id`=" . sql_escape($shift_id) . ", `angel_type_id`=" . sql_escape($type_id) . ", `count`=" . sql_escape($count));
          $needed_angel_types_info[] = $angel_types[$type_id]['name'] . ": " . $count;
        }

        engelsystem_log("Updated shift '" . $name . "' from " . date("y-m-d H:i", $start) . " to " . date("y-m-d H:i", $end) . " with angel types " . join(", ", $needed_angel_types_info));
        success("Schicht gespeichert.");
        redirect(page_link_to('user_shifts'));
      }
    }

    $room_select = html_select_key('rid', 'rid', $room_array, $rid);

    $angel_types = "";
    foreach ($types as $type) {
      $angel_types .= template_render('../templates/admin_shifts_angel_types.html', array (
        'id' => $type['id'],
        'type' => $type['name'],
        'value' => $needed_angel_types[$type['id']]
      ));
    }

    return template_render('../templates/user_shifts_edit.html', array (
      'msg' => $msg,
      'name' => $name,
      'room_select' => $room_select,
      'start' => date("Y-m-d H:i", $start),
      'end' => date("Y-m-d H:i", $end),
      'angel_types' => $angel_types
    ));
  }
  // Schicht komplett löschen (nur für admins/user mit user_shifts_admin privileg)
  elseif (isset ($_REQUEST['delete_shift']) && in_array('user_shifts_admin', $privileges)) {
    if (isset ($_REQUEST['delete_shift']) && preg_match("/^[0-9]*$/", $_REQUEST['delete_shift']))
      $shift_id = $_REQUEST['delete_shift'];
    else
      header("Location: " . page_link_to('user_shifts'));

    $shift = sql_select("SELECT * FROM `Shifts` JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `SID`=" . sql_escape($shift_id) . " LIMIT 1");
    if (count($shift) == 0)
      header("Location: " . page_link_to('user_shifts'));
    $shift = $shift[0];

    // Schicht löschen bestätigt
    if (isset ($_REQUEST['delete'])) {
      sql_query("DELETE FROM `ShiftEntry` WHERE `SID`=" . sql_escape($shift_id));
      sql_query("DELETE FROM `NeededAngelTypes` WHERE `shift_id`=" . sql_escape($shift_id));
      sql_query("DELETE FROM `Shifts` WHERE `SID`=" . sql_escape($shift_id) . " LIMIT 1");

      engelsystem_log("Deleted shift " . $shift['name'] . " from " . date("y-m-d H:i", $shift['start']) . " to " . date("y-m-d H:i", $shift['end']));
      success("Die Schicht wurde gelöscht.");
      redirect(page_link_to('user_shifts'));
    }

    return template_render('../templates/user_shifts_admin_delete.html', array (
      'name' => $shift['name'],
      'start' => date("Y-m-d H:i", $shift['start']),
      'end' => date("H:i", $shift['end']),
      'id' => $shift_id
    ));
  }
  elseif (isset ($_REQUEST['shift_id'])) {
    if (isset ($_REQUEST['shift_id']) && preg_match("/^[0-9]*$/", $_REQUEST['shift_id']))
      $shift_id = $_REQUEST['shift_id'];
    else
      header("Location: " . page_link_to('user_shifts'));

    $shift = sql_select("SELECT * FROM `Shifts` JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `SID`=" . sql_escape($shift_id) . " LIMIT 1");
    if (count($shift) == 0)
      header("Location: " . page_link_to('user_shifts'));
    $shift = $shift[0];

    if (isset ($_REQUEST['type_id']) && preg_match("/^[0-9]*$/", $_REQUEST['type_id']))
      $type_id = $_REQUEST['type_id'];
    else
      header("Location: " . page_link_to('user_shifts'));

    // Schicht läuft schon, Eintragen für Engel nicht mehr möglich
    if(!in_array('user_shifts_admin', $privileges) && time() > $shift['start']) {
      error("Diese Schicht läuft gerade oder ist bereits vorbei. Bitte kontaktiere den Schichtkoordinator um Dich eintragen zu lassen.");
      header("Location: " . page_link_to('user_shifts'));
    }

    if (in_array('user_shifts_admin', $privileges))
      $type = sql_select("SELECT * FROM `AngelTypes` WHERE `id`=" . sql_escape($type_id) . " LIMIT 1");
    else
      $type = sql_select("SELECT * FROM `UserAngelTypes` JOIN `AngelTypes` ON (`UserAngelTypes`.`angeltype_id` = `AngelTypes`.`id`) WHERE `AngelTypes`.`id` = " . sql_escape($type_id) . " AND (`AngelTypes`.`restricted` = 0 OR (`UserAngelTypes`.`user_id` = " . sql_escape($user['UID']) . " AND NOT `UserAngelTypes`.`confirm_user_id` IS NULL)) LIMIT 1");

    if (count($type) == 0)
      header("Location: " . page_link_to('user_shifts'));
    $type = $type[0];

    if (isset ($_REQUEST['submit'])) {
      $selected_type_id = $type_id;
      if (in_array('user_shifts_admin', $privileges)) {
        if (isset ($_REQUEST['user_id']) && preg_match("/^[0-9]*$/", $_REQUEST['user_id']))
          $user_id = $_REQUEST['user_id'];
        else
          $user_id = $user['UID'];

        if (sql_num_query("SELECT * FROM `User` WHERE `UID`=" . sql_escape($user_id) . " LIMIT 1") == 0)
          redirect(page_link_to('user_shifts'));

        if (isset ($_REQUEST['angeltype_id']) && test_request_int('angeltype_id') && sql_num_query("SELECT * FROM `AngelTypes` WHERE `id`=" . sql_escape($_REQUEST['angeltype_id']) . " LIMIT 1") > 0)
          $selected_type_id = $_REQUEST['angeltype_id'];
      } else
        $user_id = $user['UID'];

      // TODO: Kollisionserkennung, andere Schichten zur gleichen Uhrzeit darf der Engel auch nicht belegt haben...
      if (sql_num_query("SELECT * FROM `ShiftEntry` WHERE `SID`='" . sql_escape($shift['SID']) . "' AND `UID` = '" . sql_escape($user_id) . "'"))
        return error("This angel does already have an entry for this shift.", true);

      $comment = strip_request_item_nl('comment');
      sql_query("INSERT INTO `ShiftEntry` SET `Comment`='" . sql_escape($comment) . "', `UID`=" . sql_escape($user_id) . ", `TID`=" . sql_escape($selected_type_id) . ", `SID`=" . sql_escape($shift_id));
      if (sql_num_query("SELECT * FROM `UserAngelTypes` INNER JOIN `AngelTypes` ON `AngelTypes`.`id` = `UserAngelTypes`.`angeltype_id` WHERE `AngelTypes`.`restricted` = 0 AND `user_id` = '" . sql_escape($user_id) . "' AND `angeltype_id` = '" . sql_escape($selected_type_id) . "'") == 0)
        sql_query("INSERT INTO `UserAngelTypes` (`user_id`, `angeltype_id`) VALUES ('" . sql_escape($user_id) . "', '" . sql_escape($selected_type_id) . "')");

      $user_source = User($user_id);
      engelsystem_log("User " . $user_source['Nick'] . " signed up for shift " . $shift['name'] . " from " . date("y-m-d H:i", $shift['start']) . " to " . date("y-m-d H:i", $shift['end']));
      success("Du bist eingetragen. Danke!" . ' <a href="' . page_link_to('user_myshifts') . '">Meine Schichten &raquo;</a>');
      redirect(page_link_to('user_shifts'));
    }

    if (in_array('user_shifts_admin', $privileges)) {
      $users = sql_select("SELECT * FROM `User` ORDER BY `Nick`");
      $users_select = array ();
      foreach ($users as $usr)
        $users_select[$usr['UID']] = $usr['Nick'];
      $user_text = html_select_key('user_id', 'user_id', $users_select, $user['UID']);

      $angeltypes_source = sql_select("SELECT * FROM `AngelTypes` ORDER BY `name`");
      $angeltypes = array ();
      foreach ($angeltypes_source as $angeltype)
        $angeltypes[$angeltype['id']] = $angeltype['name'];
      $angeltyppe_select = html_select_key('angeltype_id', 'angeltype_id', $angeltypes, $type['id']);
    } else {
      $user_text = $user['Nick'];
      $angeltyppe_select = $type['name'];
    }

    return template_render('../templates/user_shifts_add.html', array (
      //'date' => date("Y-m-d H:i", $shift['start']) . ', ' . date("H:i", $shift['end'] - $shift['start']) . 'h',
      'date' => date("Y-m-d H:i", $shift['start']) . ', ' . shift_length($shift),
      'title' => $shift['name'],
      'location' => $shift['Name'],
      'angel' => $user_text,
      'type' => $angeltyppe_select,
      'comment' => ""
    ));
  } else {
    return view_user_shifts();
  }
}

function view_user_shifts() {
  global $user, $privileges;
  global $ical_shifts;

  $ical_shifts = array ();
  $days = sql_select("SELECT DISTINCT DATE(FROM_UNIXTIME(`start`)) AS `id`, DATE(FROM_UNIXTIME(`start`)) AS `name` FROM `Shifts` ORDER BY `start`");
  $rooms = sql_select("SELECT `RID` AS `id`, `Name` AS `name` FROM `Room` WHERE `show`='Y' ORDER BY `Name`");
  if (in_array('admin_shifts', $privileges))
    $types = sql_select("SELECT `id`, `name` FROM `AngelTypes` ORDER BY `AngelTypes`.`name`");
  else
    $types = sql_select("SELECT `AngelTypes`.`id`, `AngelTypes`.`name` FROM `UserAngelTypes` JOIN `AngelTypes` ON (`UserAngelTypes`.`angeltype_id` = `AngelTypes`.`id`) WHERE `UserAngelTypes`.`user_id` = " . sql_escape($user['UID']) . " AND (`AngelTypes`.`restricted` = 0 OR NOT `UserAngelTypes`.`confirm_user_id` IS NULL) ORDER BY `AngelTypes`.`name`");
  if (empty($types))
    $types = sql_select("SELECT `id`, `name` FROM `AngelTypes` WHERE `restricted` = 0");
  $filled = array (
    array (
      'id' => '1',
      'name' => Get_Text('occupied')
    ),
    array (
      'id' => '0',
      'name' => Get_Text('free')
    )
  );

  if (!isset ($_SESSION['user_shifts']))
    $_SESSION['user_shifts'] = array ();

  if (!isset ($_SESSION['user_shifts']['filled'])) {
    $_SESSION['user_shifts']['filled'] = array (
      0
    );
  }

  foreach (array (
    'rooms',
    'types',
    'filled'
  ) as $key) {
    if (isset ($_REQUEST[$key])) {
      $filtered = array_filter($_REQUEST[$key], 'is_numeric');
      if (!empty ($filtered))
        $_SESSION['user_shifts'][$key] = $filtered;
      unset ($filtered);
    }
    if (!isset ($_SESSION['user_shifts'][$key]))
      $_SESSION['user_shifts'][$key] = array_map('get_ids_from_array', $$key);
  }

  if (isset ($_REQUEST['days'])) {
    $filtered = array_filter($_REQUEST['days'], create_function('$a', 'return preg_match("/^\d\d\d\d-\d\d-\d\d\\$/", $a);'));
    if (!empty ($filtered))
      $_SESSION['user_shifts']['days'] = $filtered;
    unset ($filtered);
  }
  if (!isset ($_SESSION['user_shifts']['days']))
    $_SESSION['user_shifts']['days'] = array (
      date('Y-m-d')
    );
  if (!isset ($_SESSION['user_shifts']['rooms']) || count($_SESSION['user_shifts']['rooms']) == 0)
    $_SESSION['user_shifts']['rooms'] = array(0);

  $shifts = sql_select("SELECT `Shifts`.*, `Room`.`Name` as `room_name` FROM `Shifts` JOIN `Room` USING (`RID`)
      WHERE `Shifts`.`RID` IN (" . implode(',', $_SESSION['user_shifts']['rooms']) . ")
      AND DATE(FROM_UNIXTIME(`start`)) IN ('" . implode("','", $_SESSION['user_shifts']['days']) . "')
      ORDER BY `start`");

  $shifts_table = "";
  $row_count = 0;
  //qqqq
  /*
  [0] => Array
  (
      [SID] => 1
      [start] => 1355958000
      [end] => 1355961600
      [RID] => 1
      [name] =>
      [URL] =>
      [PSID] =>
      [room_name] => test1
  )
  */
  if(count($_SESSION['user_shifts']['days'])==1) {
    $myrooms=$rooms;
    foreach($myrooms as $k => $v) {
      if(array_search($v["id"],$_SESSION['user_shifts']['rooms'])===FALSE)
        unset($myrooms[$k]);
    }
    $first=date("U",strtotime($_SESSION['user_shifts']['days'][0]." 00:00:00"));
    $last=date("U",strtotime($_SESSION['user_shifts']['days'][0]." 23:59:59"));
    $maxshow=24*4;
    $block=array();
    foreach($myrooms as $room) {
      $rid=$room["id"];
      foreach($shifts as $shift) {
        if($shift["RID"]==$rid) {
          $blocks=($shift["end"]-$shift["start"])/(15*60);
          $firstblock=floor(($shift["start"]-$first)/(15*60));
          for($i=$firstblock;$i<$blocks+$firstblock && $i < $maxshow;$i++) {
            $block[$rid][$i]++;
          }
        }
      }
    }
    $shifts_table="<table><tr><th>-</th>";
    foreach($myrooms as $room) {
      $rid=$room["id"];
      $colspan=1;
      if(is_array($block[$rid]))
        foreach($block[$rid] as $max) if($max>$colspan) $colspan=$max;
      for($i=0;$i<$maxshow;$i++)
        $todo[$rid][$i]=$colspan;
      $shifts_table.="<th colspan=\"$colspan\">".$room['name']."</th>\n";
    }
    $shifts_table.="</tr>";
    for($i=0;$i<24*4;$i++) {
      $thistime=$first+($i*15*60);
      if($thistime%(60*60)==0) {
        $shifts_table.="<tr><th>".date("H:i",$thistime)."</th>";
      } else {
        $shifts_table.="<tr><th></th>";
      }
      foreach($myrooms as $room) {
        $rid=$room["id"];
        foreach($shifts as $shift) {
          if($shift["RID"]==$rid) {
            if(floor($shift["start"]/(15*60)) == $thistime/(15*60)) {
              $blocks=($shift["end"]-$shift["start"])/(15*60);
              if($blocks<1) $blocks=1;
              // qqqqqq
              $is_free = false;
              $shifts_row = $shift['name'];
              if (in_array('admin_shifts', $privileges))
                $shifts_row .= ' <a href="?p=user_shifts&edit_shift=' . $shift['SID'] . '">[edit]</a> <a href="?p=user_shifts&delete_shift=' . $shift['SID'] . '">[x]</a>';
              $shifts_row.= '<br />';
              $shift_has_special_needs = 0 < sql_num_query("SELECT `id` FROM `NeededAngelTypes` WHERE `shift_id` = " . $shift['SID']);
              $query = "SELECT *
              FROM `NeededAngelTypes`
              JOIN `AngelTypes`
              ON (`NeededAngelTypes`.`angel_type_id` = `AngelTypes`.`id`)
              WHERE ";
              if ($shift_has_special_needs)
                $query .= "`shift_id` = " . sql_escape($shift['SID']);
              else
                $query .= "`room_id` = " . sql_escape($shift['RID']);
              $query .= "		AND `count` > 0
              AND `angel_type_id` IN (" . implode(',', $_SESSION['user_shifts']['types']) . ")
              ORDER BY `AngelTypes`.`name`";
              $angeltypes = sql_select($query);

              if (count($angeltypes) > 0) {
                $my_shift = sql_num_query("SELECT * FROM `ShiftEntry` WHERE `SID`=" . sql_escape($shift['SID']) . " AND `UID`=" . sql_escape($user['UID']) . " LIMIT 1") > 0;
                foreach ($angeltypes as $angeltype) {
                  $entries = sql_select("SELECT * FROM `ShiftEntry` JOIN `User` ON (`ShiftEntry`.`UID` = `User`.`UID`) WHERE `SID`=" . sql_escape($shift['SID']) . " AND `TID`=" . sql_escape($angeltype['id']) . " ORDER BY `Nick`");
                  $entry_list = array ();
                  foreach ($entries as $entry) {
                    if($entry['Gekommen']==1)
                      $style="font-weight:bold;";
                    else
                      $style="font-weight:normal;";
                    if (in_array('user_shifts_admin', $privileges))
                      $entry_list[] = "<span style=\"$style\">" . '<a href="' . page_link_to('user_myshifts') . '&id=' . $entry['UID'] . '">' . $entry['Nick'] . '</a> <a href="' . page_link_to('user_shifts') . '&entry_id=' . $entry['id'] . '">[x]</a></span>';
                    else
                      $entry_list[] = "<span style=\"$style\">" . $entry['Nick']."</span>";
                  }
                  if ($angeltype['count'] - count($entries) > 0) {
                    if ((time() < $shift['end'] && !$my_shift) || in_array('user_shifts_admin', $privileges)) {
                      $entry_list[] = '<a href="' . page_link_to('user_shifts') . '&shift_id=' . $shift['SID'] . '&type_id=' . $angeltype['id'] . '">' . ($angeltype['count'] - count($entries)) . ' Helfer' . ($angeltype['count'] - count($entries) != 1 ? '' : '') . ' gebraucht &raquo;</a>';
                    } else {
                      $entry_list[] = ($angeltype['count'] - count($entries)) . ' Helfer gebraucht';
                    }
                    $is_free = true;
                  }

                  $shifts_row .= '<b>' . $angeltype['name'] . ':</b> ';
                  $shifts_row .= join(", ", $entry_list);
                  $shifts_row .= '<br />';
                }
                if (in_array('user_shifts_admin', $privileges)) {
                  $shifts_row .= '<a href="' . page_link_to('user_shifts') . '&shift_id=' . $shift['SID'] . '&type_id=' . $angeltype['id'] . '">Weitere Helfer eintragen &raquo;</a>';
                }
              }
              $color="";
              if($is_free) {
                $color="style=\"background: #F6CECE\";";
              } else {
                $color="style=\"background: #BCF5A9\";";
              }
              $shifts_table.="<td rowspan=$blocks $color>";
              if (($is_free && in_array(0, $_SESSION['user_shifts']['filled'])) || (!$is_free && in_array(1, $_SESSION['user_shifts']['filled']))) {
                $shifts_table.=$shifts_row;
              }
              $shifts_table.="</td>";
              for($j=0;$j<$blocks;$j++) {
                $todo[$rid][$i+$j]--;
              }
            }
          }
        }
        while($todo[$rid][$i]) {
          $shifts_table.='<td style="border: 1px"></td>';
          $todo[$rid][$i]--;
        }
      }
      $shifts_table.="</tr>\n";
    }
    $shifts_table.="</table>";
    // qqq
  } else foreach ($shifts as $shift) {
    $info = array ();
    if (count($_SESSION['user_shifts']['days']) > 1)
      $info[] = date("Y-m-d", $shift['start']);
    $info[] = date("H:i", $shift['start']) . ' - ' . date("H:i", $shift['end']);
    if (count($_SESSION['user_shifts']['rooms']) > 1)
      $info[] = $shift['room_name'];
    $shift_row = '<tr><td>' . join('<br />', $info) . '</td>';
    $shift_row .= '<td>' . $shift['name'];

    if (in_array('admin_shifts', $privileges))
      $shift_row .= ' <a href="?p=user_shifts&edit_shift=' . $shift['SID'] . '">[edit]</a> <a href="?p=user_shifts&delete_shift=' . $shift['SID'] . '">[x]</a>';
    $shift_row .= '<br />';
    $is_free = false;
    $shift_has_special_needs = 0 < sql_num_query("SELECT `id` FROM `NeededAngelTypes` WHERE `shift_id` = " . $shift['SID']);
    $query = "SELECT *
    FROM `NeededAngelTypes`
    JOIN `AngelTypes`
    ON (`NeededAngelTypes`.`angel_type_id` = `AngelTypes`.`id`)
    WHERE ";
    if ($shift_has_special_needs)
      $query .= "`shift_id` = " . sql_escape($shift['SID']);
    else
      $query .= "`room_id` = " . sql_escape($shift['RID']);
    $query .= "		AND `count` > 0 ";
    if (!empty($_SESSION['user_shifts']['types']))
      $query .= "AND `angel_type_id` IN (" . implode(',', $_SESSION['user_shifts']['types']) . ") ";
    $query .= "ORDER BY `AngelTypes`.`name`";
    $angeltypes = sql_select($query);

    if (count($angeltypes) > 0) {
      $my_shift = sql_num_query("SELECT * FROM `ShiftEntry` WHERE `SID`=" . sql_escape($shift['SID']) . " AND `UID`=" . sql_escape($user['UID']) . " LIMIT 1") > 0;
      foreach ($angeltypes as $angeltype) {
        $entries = sql_select("SELECT * FROM `ShiftEntry` JOIN `User` ON (`ShiftEntry`.`UID` = `User`.`UID`) WHERE `SID`=" . sql_escape($shift['SID']) . " AND `TID`=" . sql_escape($angeltype['id']) . " ORDER BY `Nick`");
        $entry_list = array ();
        foreach ($entries as $entry) {
          if (in_array('user_shifts_admin', $privileges))
            $entry_list[] = '<a href="' . page_link_to('user_myshifts') . '&id=' . $entry['UID'] . '">' . $entry['Nick'] . '</a> <a href="' . page_link_to('user_shifts') . '&entry_id=' . $entry['id'] . '">[x]</a>';
          else
            $entry_list[] = $entry['Nick'];
        }
        // do we need more angles of this type?
        if ($angeltype['count'] - count($entries) > 0) {
          $inner_text = ($angeltype['count'] - count($entries)) . ' ' . Get_Text($angeltype['count'] - count($entries) == 1 ? 'helper' : 'helpers') . ' ' . Get_Text('needed');
          // is the shift still running or alternatively is the user shift admin?
          if ((time() < $shift['end'] && !$my_shift) || in_array('user_shifts_admin', $privileges))
            $entry_list[] = '<a href="' . page_link_to('user_shifts') . '&shift_id=' . $shift['SID'] . '&type_id=' . $angeltype['id'] . '">' . $inner_text . ' &raquo;</a>';
          else
            $entry_list[] = $inner_text;
          unset($inner_text);
          $is_free = true;
        }

        $shift_row .= '<b>' . $angeltype['name'] . ':</b> ';
        $shift_row .= join(", ", $entry_list);
        $shift_row .= '<br />';
      }
      if (in_array('user_shifts_admin', $privileges)) {
        $shift_row .= '<a href="' . page_link_to('user_shifts') . '&shift_id=' . $shift['SID'] . '&type_id=' . $angeltype['id'] . '">Weitere Helfer eintragen &raquo;</a>';
      }
      if (($is_free && in_array(0, $_SESSION['user_shifts']['filled'])) || (!$is_free && in_array(1, $_SESSION['user_shifts']['filled']))) {
        $shifts_table .= $shift_row . '</td></tr>';
        $row_count++;
        $ical_shifts[] = $shift;
      }
    }
  }

  if ($user['ical_key'] == "")
    user_reset_ical_key($user);

  return msg() . template_render('../templates/user_shifts.html', array (
    'room_select' => make_select($rooms, $_SESSION['user_shifts']['rooms'], "rooms", ucfirst(Get_Text("rooms"))),
    'day_select' => make_select($days, $_SESSION['user_shifts']['days'], "days", ucfirst(Get_Text("days"))),
    'type_select' => make_select($types, $_SESSION['user_shifts']['types'], "types", ucfirst(Get_Text("tasks")) . '<sup>1</sup>'),
    'filled_select' => make_select($filled, $_SESSION['user_shifts']['filled'], "filled", ucfirst(Get_Text("occupancy"))),
    'task_notice' => '<sup>1</sup>' . Get_Text("pub_schichtplan_tasks_notice"),
    'shifts_table' => $shifts_table,
    'ical_text' => sprintf(Get_Text('inc_schicht_ical_text'), make_user_shifts_ical_link($user['ical_key']), page_link_to('user_myshifts') . '&reset'),
    'header1' => ucfirst(Get_Text("time")) . "/" . ucfirst(Get_Text("room")),
    'header2' => ucfirst(Get_Text("entries")),
    'filter' => ucfirst(Get_Text("to_filter")),
  ));
}

function make_user_shifts_ical_link($key) {
  $link = "";
  foreach ($_SESSION['user_shifts']['rooms'] as $room)
    $link .= '&rooms[]=' . $room;
  foreach ($_SESSION['user_shifts']['days'] as $day)
    $link .= '&days[]=' . $day;
  foreach ($_SESSION['user_shifts']['types'] as $type)
    $link .= '&types[]=' . $type;
  foreach ($_SESSION['user_shifts']['filled'] as $filled)
    $link .= '&filled[]=' . $filled;
  return page_link_to_absolute('ical') . $link . '&export=user_shifts&key=' . $key;
}

function get_ids_from_array($array) {
  return $array["id"];
}

function make_select($items, $selected, $name, $title = null) {
  $html_items = array ();
  if (isset ($title))
    $html_items[] = '<li class="heading">' . $title . '</li>' . "\n";

  foreach ($items as $i)
    $html_items[] = '<li><label><input type="checkbox" name="' . $name . '[]" value="' . $i['id'] . '"' . (in_array($i['id'], $selected) ? ' checked="checked"' : '') . '> ' . $i['name'] . '</label></li>';
  $html = '<div class="selection ' . $name . '">' . "\n";
  $html .= '<ul id="selection_' . $name . '">' . "\n";
  $html .= implode("\n", $html_items);
  $html .= '</ul>' . "\n";
  $html .= buttons(array (
    button("javascript: check_all('selection_" . $name . "')", Get_Text("all"), ""),
    button("javascript: uncheck_all('selection_" . $name . "')", Get_Text("none"), "")
  ));
  $html .= '</div>' . "\n";
  return $html;
}
?>
