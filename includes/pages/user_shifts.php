<?php

function shifts_title() {
  return _("Shifts");
}

/**
 * Start different controllers for deleting shifts and shift_entries, edit shifts and add shift entries.
 */
function user_shifts() {
  global $user, $privileges;
  
  if (User_is_freeloader($user)) {
    redirect(page_link_to('user_myshifts'));
  }
  
  // Löschen einzelner Schicht-Einträge (Also Belegung einer Schicht von Engeln) durch Admins
  if (isset($_REQUEST['entry_id'])) {
    return shift_entry_delete_controller();
  } elseif (isset($_REQUEST['edit_shift'])) {
    return shift_edit_controller();
  } elseif (isset($_REQUEST['delete_shift'])) {
    return shift_delete_controller();
  } elseif (isset($_REQUEST['shift_id'])) {
    return shift_entry_add_controller();
  }
  return view_user_shifts();
}

function view_user_shifts() {
  global $user, $privileges;
  global $ical_shifts;
  
  $ical_shifts = [];
  $days = sql_select_single_col("
      SELECT DISTINCT DATE(FROM_UNIXTIME(`start`)) AS `id`, DATE(FROM_UNIXTIME(`start`)) AS `name`
      FROM `Shifts`
      ORDER BY `start`");
  
  if (count($days) == 0) {
    error(_("The administration has not configured any shifts yet."));
    redirect('?');
  }
  
  $rooms = sql_select("SELECT `RID` AS `id`, `Name` AS `name` FROM `Room` WHERE `show`='Y' ORDER BY `Name`");
  
  if (count($rooms) == 0) {
    error(_("The administration has not configured any rooms yet."));
    redirect('?');
  }
  
  if (in_array('user_shifts_admin', $privileges)) {
    $types = sql_select("SELECT `id`, `name` FROM `AngelTypes` ORDER BY `AngelTypes`.`name`");
  } else {
    $types = sql_select("SELECT `AngelTypes`.`id`, `AngelTypes`.`name`, (`AngelTypes`.`restricted`=0 OR (NOT `UserAngelTypes`.`confirm_user_id` IS NULL OR `UserAngelTypes`.`id` IS NULL)) as `enabled` FROM `AngelTypes` LEFT JOIN `UserAngelTypes` ON (`UserAngelTypes`.`angeltype_id`=`AngelTypes`.`id` AND `UserAngelTypes`.`user_id`='" . sql_escape($user['UID']) . "') ORDER BY `AngelTypes`.`name`");
  }
  if (empty($types)) {
    $types = sql_select("SELECT `id`, `name` FROM `AngelTypes` WHERE `restricted` = 0");
  }
  $filled = [
      [
          'id' => '1',
          'name' => _("occupied") 
      ],
      [
          'id' => '0',
          'name' => _("free") 
      ] 
  ];
  
  if (count($types) == 0) {
    error(_("The administration has not configured any angeltypes yet - or you are not subscribed to any angeltype."));
    redirect('?');
  }
  
  if (! isset($_SESSION['user_shifts'])) {
    $_SESSION['user_shifts'] = [];
  }
  
  if (! isset($_SESSION['user_shifts']['filled'])) {
    // User shift admins see free and occupied shifts by default
    $_SESSION['user_shifts']['filled'] = in_array('user_shifts_admin', $privileges) ? [
        0,
        1 
    ] : [
        0 
    ];
  }
  
  foreach ([
      'rooms',
      'types',
      'filled' 
  ] as $key) {
    if (isset($_REQUEST[$key])) {
      $filtered = array_filter($_REQUEST[$key], 'is_numeric');
      if (! empty($filtered)) {
        $_SESSION['user_shifts'][$key] = $filtered;
      }
      unset($filtered);
    }
    if (! isset($_SESSION['user_shifts'][$key])) {
      $_SESSION['user_shifts'][$key] = array_map('get_ids_from_array', $$key);
    }
  }
  
  if (isset($_REQUEST['rooms'])) {
    if (isset($_REQUEST['new_style'])) {
      $_SESSION['user_shifts']['new_style'] = true;
    } else {
      $_SESSION['user_shifts']['new_style'] = false;
    }
  }
  if (! isset($_SESSION['user_shifts']['new_style'])) {
    $_SESSION['user_shifts']['new_style'] = true;
  }
  foreach ([
      'start',
      'end' 
  ] as $key) {
    if (isset($_REQUEST[$key . '_day']) && in_array($_REQUEST[$key . '_day'], $days)) {
      $_SESSION['user_shifts'][$key . '_day'] = $_REQUEST[$key . '_day'];
    }
    if (isset($_REQUEST[$key . '_time']) && preg_match('#^\d{1,2}:\d\d$#', $_REQUEST[$key . '_time'])) {
      $_SESSION['user_shifts'][$key . '_time'] = $_REQUEST[$key . '_time'];
    }
    if (! isset($_SESSION['user_shifts'][$key . '_day'])) {
      $time = date('Y-m-d', time() + ($key == 'end' ? 24 * 60 * 60 : 0));
      $_SESSION['user_shifts'][$key . '_day'] = in_array($time, $days) ? $time : ($key == 'end' ? max($days) : min($days));
    }
    if (! isset($_SESSION['user_shifts'][$key . '_time'])) {
      $_SESSION['user_shifts'][$key . '_time'] = date('H:i');
    }
  }
  if ($_SESSION['user_shifts']['start_day'] > $_SESSION['user_shifts']['end_day']) {
    $_SESSION['user_shifts']['end_day'] = $_SESSION['user_shifts']['start_day'];
  }
  if ($_SESSION['user_shifts']['start_day'] == $_SESSION['user_shifts']['end_day'] && $_SESSION['user_shifts']['start_time'] >= $_SESSION['user_shifts']['end_time']) {
    $_SESSION['user_shifts']['end_time'] = '23:59';
  }

  $starttime = now();
  if (isset($_SESSION['user_shifts']['start_day'])) {
    $starttime = DateTime::createFromFormat("Y-m-d H:i", $_SESSION['user_shifts']['start_day'] . $_SESSION['user_shifts']['start_time']);
    $starttime = $starttime->getTimestamp();
  }

  $endtime = now() + 24 * 60 * 60;
  if (isset($_SESSION['user_shifts']['end_day'])) {
    $endtime = DateTime::createFromFormat("Y-m-d H:i", $_SESSION['user_shifts']['end_day'] . $_SESSION['user_shifts']['end_time']);
    $endtime = $endtime->getTimestamp();
  }
  
  if (! isset($_SESSION['user_shifts']['rooms']) || count($_SESSION['user_shifts']['rooms']) == 0) {
    $_SESSION['user_shifts']['rooms'] = [
        0 
    ];
  }
  
  $SQL = "SELECT DISTINCT `Shifts`.*, `ShiftTypes`.`name`, `Room`.`Name` as `room_name`, nat2.`special_needs` > 0 AS 'has_special_needs'
  FROM `Shifts`
  INNER JOIN `Room` USING (`RID`)
  INNER JOIN `ShiftTypes` ON (`ShiftTypes`.`id` = `Shifts`.`shifttype_id`)
  LEFT JOIN (SELECT COUNT(*) AS special_needs , nat3.`shift_id` FROM `NeededAngelTypes` AS nat3 WHERE `shift_id` IS NOT NULL GROUP BY nat3.`shift_id`) AS nat2 ON nat2.`shift_id` = `Shifts`.`SID`
  INNER JOIN `NeededAngelTypes` AS nat ON nat.`count` != 0 AND nat.`angel_type_id` IN (" . implode(',', $_SESSION['user_shifts']['types']) . ") AND ((nat2.`special_needs` > 0 AND nat.`shift_id` = `Shifts`.`SID`) OR ((nat2.`special_needs` = 0 OR nat2.`special_needs` IS NULL) AND nat.`room_id` = `RID`))
  LEFT JOIN (SELECT se.`SID`, se.`TID`, COUNT(*) as count FROM `ShiftEntry` AS se GROUP BY se.`SID`, se.`TID`) AS entries ON entries.`SID` = `Shifts`.`SID` AND entries.`TID` = nat.`angel_type_id`
  WHERE `Shifts`.`RID` IN (" . implode(',', $_SESSION['user_shifts']['rooms']) . ")
  AND `start` BETWEEN " . $starttime . " AND " . $endtime;
  
  if (count($_SESSION['user_shifts']['filled']) == 1) {
    if ($_SESSION['user_shifts']['filled'][0] == 0) {
      $SQL .= "
      AND (nat.`count` > entries.`count` OR entries.`count` IS NULL OR EXISTS (SELECT `SID` FROM `ShiftEntry` WHERE `UID` = '" . sql_escape($user['UID']) . "' AND `ShiftEntry`.`SID` = `Shifts`.`SID`))";
    } elseif ($_SESSION['user_shifts']['filled'][0] == 1) {
      $SQL .= "
    AND (nat.`count` <= entries.`count`  OR EXISTS (SELECT `SID` FROM `ShiftEntry` WHERE `UID` = '" . sql_escape($user['UID']) . "' AND `ShiftEntry`.`SID` = `Shifts`.`SID`))";
    }
  }
  $SQL .= "
  ORDER BY `start`";
  
  $shifts = sql_select($SQL);
  
  $ownshifts_source = sql_select("
      SELECT `ShiftTypes`.`name`, `Shifts`.*
      FROM `Shifts`
      INNER JOIN `ShiftTypes` ON (`ShiftTypes`.`id` = `Shifts`.`shifttype_id`)
      INNER JOIN `ShiftEntry` ON (`Shifts`.`SID` = `ShiftEntry`.`SID` AND `ShiftEntry`.`UID` = '" . sql_escape($user['UID']) . "')
      WHERE `Shifts`.`RID` IN (" . implode(',', $_SESSION['user_shifts']['rooms']) . ")
      AND `start` BETWEEN " . $starttime . " AND " . $endtime);
  $ownshifts = [];
  foreach ($ownshifts_source as $ownshift) {
    $ownshifts[$ownshift['SID']] = $ownshift;
  }
  unset($ownshifts_source);
  
  $shifts_table = "";
  /*
   * [0] => Array ( [SID] => 1 [start] => 1355958000 [end] => 1355961600 [RID] => 1 [name] => [URL] => [PSID] => [room_name] => test1 [has_special_needs] => 1 [is_full] => 0 )
   */
  if ($_SESSION['user_shifts']['new_style']) {
    $first = 15 * 60 * floor($starttime / (15 * 60));
    $maxshow = ceil(($endtime - $first) / (60 * 15));
    $block = [];
    $todo = [];
    $myrooms = $rooms;
    
    // delete un-selected rooms from array
    foreach ($myrooms as $k => $v) {
      if (array_search($v["id"], $_SESSION['user_shifts']['rooms']) === false) {
        unset($myrooms[$k]);
      }
      // initialize $block array
      $block[$v["id"]] = array_fill(0, $maxshow, 0);
    }
    
    // calculate number of parallel shifts in each timeslot for each room
    foreach ($shifts as $k => $shift) {
      $rid = $shift["RID"];
      $blocks = ($shift["end"] - $shift["start"]) / (15 * 60);
      $firstblock = floor(($shift["start"] - $first) / (15 * 60));
      for ($i = $firstblock; $i < $blocks + $firstblock && $i < $maxshow; $i ++) {
        $block[$rid][$i] ++;
      }
      $shifts[$k]['own'] = in_array($shift['SID'], array_keys($ownshifts));
    }
    
    $shifts_table = '<div class="shifts-table"><table id="shifts" class="table scrollable"><thead><tr><th>-</th>';
    foreach ($myrooms as $key => $room) {
      $rid = $room["id"];
      if (array_sum($block[$rid]) == 0) {
        // do not display columns without entries
        unset($block[$rid]);
        unset($myrooms[$key]);
        continue;
      }
      $colspan = call_user_func_array('max', $block[$rid]);
      if ($colspan == 0) {
        $colspan = 1;
      }
      $todo[$rid] = array_fill(0, $maxshow, $colspan);
      $shifts_table .= "<th" . (($colspan > 1) ? ' colspan="' . $colspan . '"' : '') . ">" . Room_name_render([
          'RID' => $room['id'],
          'Name' => $room['name'] 
      ]) . "</th>\n";
    }
    unset($block, $blocks, $firstblock, $colspan, $key, $room);
    
    $shifts_table .= "</tr></thead><tbody>";
    for ($i = 0; $i < $maxshow; $i ++) {
      $thistime = $first + ($i * 15 * 60);
      if ($thistime % (24 * 60 * 60) == 23 * 60 * 60 && $endtime - $starttime > 24 * 60 * 60) {
        $shifts_table .= "<tr class=\"row-day\"><th class=\"row-header\">";
        $shifts_table .= date('Y-m-d<b\r />H:i', $thistime);
      } elseif ($thistime % (60 * 60) == 0) {
        $shifts_table .= "<tr class=\"row-hour\"><th>";
        $shifts_table .= date("H:i", $thistime);
      } else {
        $shifts_table .= "<tr><th>";
      }
      $shifts_table .= "</th>";
      foreach ($myrooms as $room) {
        $rid = $room["id"];
        foreach ($shifts as $shift) {
          if ($shift["RID"] == $rid) {
            if (floor($shift["start"] / (15 * 60)) == $thistime / (15 * 60)) {
              $blocks = ($shift["end"] - $shift["start"]) / (15 * 60);
              if ($blocks < 1) {
                $blocks = 1;
              }
              
              $collides = in_array($shift['SID'], array_keys($ownshifts));
              if (! $collides) {
                foreach ($ownshifts as $ownshift) {
                  if ($ownshift['start'] >= $shift['start'] && $ownshift['start'] < $shift['end'] || $ownshift['end'] > $shift['start'] && $ownshift['end'] <= $shift['end'] || $ownshift['start'] < $shift['start'] && $ownshift['end'] > $shift['end']) {
                    $collides = true;
                    break;
                  }
                }
              }
              
              $is_free = false;
              $shifts_row = '';
              if (in_array('admin_shifts', $privileges)) {
                $shifts_row .= '<div class="pull-right">' . table_buttons([
                    button(page_link_to('user_shifts') . '&edit_shift=' . $shift['SID'], glyph('edit'), 'btn-xs'),
                    button(page_link_to('user_shifts') . '&delete_shift=' . $shift['SID'], glyph('trash'), 'btn-xs') 
                ]) . '</div>';
              }
              $shifts_row .= Room_name_render([
                  'RID' => $room['id'],
                  'Name' => $room['name'] 
              ]) . '<br />';
              $shifts_row .= '<a href="' . shift_link($shift) . '">' . date('Y-m-d H:i', $shift['start']);
              $shifts_row .= " &ndash; ";
              $shifts_row .= date('H:i', $shift['end']);
              $shifts_row .= "<br /><b>";
              $shifts_row .= ShiftType($shift['shifttype_id'])['name'];
              $shifts_row .= "</b><br />";
              if ($shift['title'] != '') {
                $shifts_row .= $shift['title'];
                $shifts_row .= "<br />";
              }
              $shifts_row .= '</a>';
              $shifts_row .= '<br />';
              $query = "SELECT `NeededAngelTypes`.`count`, `AngelTypes`.`id`, `AngelTypes`.`restricted`, `UserAngelTypes`.`confirm_user_id`, `AngelTypes`.`name`, `UserAngelTypes`.`user_id`
            FROM `NeededAngelTypes`
            JOIN `AngelTypes` ON (`NeededAngelTypes`.`angel_type_id` = `AngelTypes`.`id`)
            LEFT JOIN `UserAngelTypes` ON (`NeededAngelTypes`.`angel_type_id` = `UserAngelTypes`.`angeltype_id`AND `UserAngelTypes`.`user_id`='" . sql_escape($user['UID']) . "')
            WHERE
            `count` > 0
            AND ";
              if ($shift['has_special_needs']) {
                $query .= "`shift_id` = '" . sql_escape($shift['SID']) . "'";
              } else {
                $query .= "`room_id` = '" . sql_escape($shift['RID']) . "'";
              }
              if (! empty($_SESSION['user_shifts']['types'])) {
                $query .= " AND `angel_type_id` IN (" . implode(',', $_SESSION['user_shifts']['types']) . ") ";
              }
              $query .= " ORDER BY `AngelTypes`.`name`";
              $angeltypes = sql_select($query);
              
              if (count($angeltypes) > 0) {
                foreach ($angeltypes as $angeltype) {
                  $entries = sql_select("SELECT * FROM `ShiftEntry` JOIN `User` ON (`ShiftEntry`.`UID` = `User`.`UID`) WHERE `SID`='" . sql_escape($shift['SID']) . "' AND `TID`='" . sql_escape($angeltype['id']) . "' ORDER BY `Nick`");
                  $entry_list = [];
                  $freeloader = 0;
                  foreach ($entries as $entry) {
                    $style = '';
                    if ($entry['freeloaded']) {
                      $freeloader ++;
                      $style = " text-decoration: line-through;";
                    }
                    if (in_array('user_shifts_admin', $privileges)) {
                      $entry_list[] = "<span style=\"$style\">" . User_Nick_render($entry) . ' ' . table_buttons([
                          button(page_link_to('user_shifts') . '&entry_id=' . $entry['id'], glyph('trash'), 'btn-xs') 
                      ]) . '</span>';
                    } else {
                      $entry_list[] = "<span style=\"$style\">" . User_Nick_render($entry) . "</span>";
                    }
                  }
                  if ($angeltype['count'] - count($entries) - $freeloader > 0) {
                    $inner_text = sprintf(ngettext("%d helper needed", "%d helpers needed", $angeltype['count'] - count($entries)), $angeltype['count'] - count($entries));
                    // is the shift still running or alternatively is the user shift admin?
                    $user_may_join_shift = true;
                    
                    // you cannot join if user alread joined a parallel or this shift
                    $user_may_join_shift &= ! $collides;
                    
                    // you cannot join if user is not of this angel type
                    $user_may_join_shift &= isset($angeltype['user_id']);
                    
                    // you cannot join if you are not confirmed
                    if ($angeltype['restricted'] == 1 && isset($angeltype['user_id'])) {
                      $user_may_join_shift &= isset($angeltype['confirm_user_id']);
                    }
                    
                    // you can only join if the shift is in future or running
                    $user_may_join_shift &= time() < $shift['start'];
                    
                    // User shift admins may join anybody in every shift
                    $user_may_join_shift |= in_array('user_shifts_admin', $privileges);
                    if ($user_may_join_shift) {
                      $entry_list[] = '<a href="' . page_link_to('user_shifts') . '&amp;shift_id=' . $shift['SID'] . '&amp;type_id=' . $angeltype['id'] . '">' . $inner_text . '</a> ' . button(page_link_to('user_shifts') . '&amp;shift_id=' . $shift['SID'] . '&amp;type_id=' . $angeltype['id'], _('Sign up'), 'btn-xs');
                    } else {
                      if (time() > $shift['start']) {
                        $entry_list[] = $inner_text . ' (' . _('ended') . ')';
                      } elseif ($angeltype['restricted'] == 1 && isset($angeltype['user_id']) && ! isset($angeltype['confirm_user_id'])) {
                        $entry_list[] = $inner_text . glyph('lock');
                      } elseif ($angeltype['restricted'] == 1) {
                        $entry_list[] = $inner_text;
                      } elseif ($collides) {
                        $entry_list[] = $inner_text;
                      } else {
                        $entry_list[] = $inner_text . '<br />' . button(page_link_to('user_angeltypes') . '&action=add&angeltype_id=' . $angeltype['id'], sprintf(_('Become %s'), $angeltype['name']), 'btn-xs');
                      }
                    }
                    
                    unset($inner_text);
                    $is_free = true;
                  }
                  
                  $shifts_row .= '<strong>' . AngelType_name_render($angeltype) . ':</strong> ';
                  $shifts_row .= join(", ", $entry_list);
                  $shifts_row .= '<br />';
                }
                if (in_array('user_shifts_admin', $privileges)) {
                  $shifts_row .= ' ' . button(page_link_to('user_shifts') . '&amp;shift_id=' . $shift['SID'] . '&amp;type_id=' . $angeltype['id'], _("Add more angels"), 'btn-xs');
                }
              }
              if ($shift['own'] && ! in_array('user_shifts_admin', $privileges)) {
                $class = 'own';
              } elseif ($collides && ! in_array('user_shifts_admin', $privileges)) {
                $class = 'collides';
              } elseif ($is_free) {
                $class = 'free';
              } else {
                $class = 'occupied';
              }
              $shifts_table .= '<td rowspan="' . $blocks . '" class="' . $class . '">';
              $shifts_table .= $shifts_row;
              $shifts_table .= "</td>";
              for ($j = 0; $j < $blocks && $i + $j < $maxshow; $j ++) {
                $todo[$rid][$i + $j] --;
              }
            }
          }
        }
        // fill up row with empty <td>
        while ($todo[$rid][$i] -- > 0) {
          $shifts_table .= '<td class="empty"></td>';
        }
      }
      $shifts_table .= "</tr>\n";
    }
    $shifts_table .= '</tbody></table></div>';
  } else {
    $shifts_table = [];
    foreach ($shifts as $shift) {
      $info = [];
      if ($_SESSION['user_shifts']['start_day'] != $_SESSION['user_shifts']['end_day']) {
        $info[] = date("Y-m-d", $shift['start']);
      }
      $info[] = date("H:i", $shift['start']) . ' - ' . date("H:i", $shift['end']);
      if (count($_SESSION['user_shifts']['rooms']) > 1) {
        $info[] = Room_name_render([
            'Name' => $shift['room_name'],
            'RID' => $shift['RID'] 
        ]);
      }
      
      $shift_row = [
          'info' => join('<br />', $info),
          'entries' => '<a href="' . shift_link($shift) . '">' . $shift['name'] . '</a>' . ($shift['title'] ? '<br />' . $shift['title'] : '') 
      ];
      
      if (in_array('admin_shifts', $privileges)) {
        $shift_row['info'] .= ' ' . table_buttons([
            button(page_link_to('user_shifts') . '&edit_shift=' . $shift['SID'], glyph('edit'), 'btn-xs'),
            button(page_link_to('user_shifts') . '&delete_shift=' . $shift['SID'], glyph('trash'), 'btn-xs') 
        ]);
      }
      $shift_row['entries'] .= '<br />';
      $is_free = false;
      $shift_has_special_needs = 0 < sql_num_query("SELECT `id` FROM `NeededAngelTypes` WHERE `shift_id` = " . $shift['SID']);
      $query = "SELECT `NeededAngelTypes`.`count`, `AngelTypes`.`id`, `AngelTypes`.`restricted`, `UserAngelTypes`.`confirm_user_id`, `AngelTypes`.`name`, `UserAngelTypes`.`user_id`
    FROM `NeededAngelTypes`
    JOIN `AngelTypes` ON (`NeededAngelTypes`.`angel_type_id` = `AngelTypes`.`id`)
    LEFT JOIN `UserAngelTypes` ON (`NeededAngelTypes`.`angel_type_id` = `UserAngelTypes`.`angeltype_id`AND `UserAngelTypes`.`user_id`='" . sql_escape($user['UID']) . "')
    WHERE ";
      if ($shift_has_special_needs) {
        $query .= "`shift_id` = '" . sql_escape($shift['SID']) . "'";
      } else {
        $query .= "`room_id` = '" . sql_escape($shift['RID']) . "'";
      }
      $query .= "               AND `count` > 0 ";
      if (! empty($_SESSION['user_shifts']['types'])) {
        $query .= "AND `angel_type_id` IN (" . implode(',', $_SESSION['user_shifts']['types']) . ") ";
      }
      $query .= "ORDER BY `AngelTypes`.`name`";
      $angeltypes = sql_select($query);
      if (count($angeltypes) > 0) {
        $my_shift = sql_num_query("SELECT * FROM `ShiftEntry` WHERE `SID`='" . sql_escape($shift['SID']) . "' AND `UID`='" . sql_escape($user['UID']) . "' LIMIT 1") > 0;
        
        foreach ($angeltypes as &$angeltype) {
          $entries = sql_select("SELECT * FROM `ShiftEntry` JOIN `User` ON (`ShiftEntry`.`UID` = `User`.`UID`) WHERE `SID`='" . sql_escape($shift['SID']) . "' AND `TID`='" . sql_escape($angeltype['id']) . "' ORDER BY `Nick`");
          $entry_list = [];
          $entry_nicks = [];
          $freeloader = 0;
          foreach ($entries as $entry) {
            if (in_array('user_shifts_admin', $privileges)) {
              $member = User_Nick_render($entry) . ' ' . table_buttons([
                  button(page_link_to('user_shifts') . '&entry_id=' . $entry['id'], glyph('trash'), 'btn-xs') 
              ]);
            } else {
              $member = User_Nick_render($entry);
            }
            if ($entry['freeloaded']) {
              $member = '<strike>' . $member . '</strike>';
              $freeloader ++;
            }
            $entry_list[] = $member;
            $entry_nicks[] = $entry['Nick'];
          }
          $angeltype['taken'] = count($entries) - $freeloader;
          $angeltype['angels'] = $entry_nicks;
          
          // do we need more angles of this type?
          if ($angeltype['count'] - count($entries) + $freeloader > 0) {
            $inner_text = sprintf(ngettext("%d helper needed", "%d helpers needed", $angeltype['count'] - count($entries) + $freeloader), $angeltype['count'] - count($entries) + $freeloader);
            // is the shift still running or alternatively is the user shift admin?
            $user_may_join_shift = true;
            
            /* you cannot join if user already joined this shift */
            $user_may_join_shift &= ! $my_shift;
            
            // you cannot join if user is not of this angel type
            $user_may_join_shift &= isset($angeltype['user_id']);
            
            // you cannot join if you are not confirmed
            if ($angeltype['restricted'] == 1 && isset($angeltype['user_id'])) {
              $user_may_join_shift &= isset($angeltype['confirm_user_id']);
            }
            
            // you can only join if the shift is in future or running
            $user_may_join_shift &= time() < $shift['start'];
            
            // User shift admins may join anybody in every shift
            $user_may_join_shift |= in_array('user_shifts_admin', $privileges);
            if ($user_may_join_shift) {
              $entry_list[] = '<a href="' . page_link_to('user_shifts') . '&amp;shift_id=' . $shift['SID'] . '&amp;type_id=' . $angeltype['id'] . '">' . $inner_text . ' &raquo;</a>';
            } else {
              if (time() > $shift['end']) {
                $entry_list[] = $inner_text . ' (vorbei)';
              } elseif ($angeltype['restricted'] == 1 && isset($angeltype['user_id']) && ! isset($angeltype['confirm_user_id'])) {
                $entry_list[] = $inner_text . glyph("lock");
              } else {
                $entry_list[] = $inner_text . ' <a href="' . page_link_to('user_angeltypes') . '&action=add&angeltype_id=' . $angeltype['id'] . '">' . sprintf(_('Become %s'), $angeltype['name']) . '</a>';
              }
            }
            
            unset($inner_text);
            $is_free = true;
          }
          
          $shift_row['entries'] .= '<b>' . $angeltype['name'] . ':</b> ';
          $shift_row['entries'] .= join(", ", $entry_list);
          $shift_row['entries'] .= '<br />';
        }
        if (in_array('user_shifts_admin', $privileges)) {
          $shift_row['entries'] .= '<a href="' . page_link_to('user_shifts') . '&amp;shift_id=' . $shift['SID'] . '&amp;type_id=' . $angeltype['id'] . '">' . _('Add more angels') . ' &raquo;</a>';
        }
        $shifts_table[] = $shift_row;
        $shift['angeltypes'] = $angeltypes;
        $ical_shifts[] = $shift;
      }
    }
    $shifts_table = table([
        'info' => _("Time") . "/" . _("Room"),
        'entries' => _("Entries") 
    ], $shifts_table);
  }
  
  if ($user['api_key'] == "") {
    User_reset_api_key($user, false);
  }
  
  return page([
      div('col-md-12', [
          msg(),
          template_render('../templates/user_shifts.html', [
              'title' => shifts_title(),
              'room_select' => make_select($rooms, $_SESSION['user_shifts']['rooms'], "rooms", _("Rooms")),
              'start_select' => html_select_key("start_day", "start_day", array_combine($days, $days), $_SESSION['user_shifts']['start_day']),
              'start_time' => $_SESSION['user_shifts']['start_time'],
              'end_select' => html_select_key("end_day", "end_day", array_combine($days, $days), $_SESSION['user_shifts']['end_day']),
              'end_time' => $_SESSION['user_shifts']['end_time'],
              'type_select' => make_select($types, $_SESSION['user_shifts']['types'], "types", _("Angeltypes") . '<sup>1</sup>'),
              'filled_select' => make_select($filled, $_SESSION['user_shifts']['filled'], "filled", _("Occupancy")),
              'task_notice' => '<sup>1</sup>' . _("The tasks shown here are influenced by the preferences you defined in your settings!") . " <a href=\"" . page_link_to('angeltypes') . '&action=about' . "\">" . _("Description of the jobs.") . "</a>",
              'new_style_checkbox' => '<label><input type="checkbox" name="new_style" value="1" ' . ($_SESSION['user_shifts']['new_style'] ? ' checked' : '') . '> ' . _("Use new style if possible") . '</label>',
              'shifts_table' => msg() . $shifts_table,
              'ical_text' => '<h2>' . _("iCal export") . '</h2><p>' . sprintf(_("Export of shown shifts. <a href=\"%s\">iCal format</a> or <a href=\"%s\">JSON format</a> available (please keep secret, otherwise <a href=\"%s\">reset the api key</a>)."), page_link_to_absolute('ical') . '&key=' . $user['api_key'], page_link_to_absolute('shifts_json_export') . '&key=' . $user['api_key'], page_link_to('user_myshifts') . '&reset') . '</p>',
              'filter' => _("Filter") 
          ]) 
      ]) 
  ]);
}

function make_user_shifts_export_link($page, $key) {
  $link = "&start_day=" . $_SESSION['user_shifts']['start_day'];
  $link = "&start_time=" . $_SESSION['user_shifts']['start_time'];
  $link = "&end_day=" . $_SESSION['user_shifts']['end_day'];
  $link = "&end_time=" . $_SESSION['user_shifts']['end_time'];
  foreach ($_SESSION['user_shifts']['rooms'] as $room) {
    $link .= '&rooms[]=' . $room;
  }
  foreach ($_SESSION['user_shifts']['types'] as $type) {
    $link .= '&types[]=' . $type;
  }
  foreach ($_SESSION['user_shifts']['filled'] as $filled) {
    $link .= '&filled[]=' . $filled;
  }
  return page_link_to_absolute($page) . $link . '&export=user_shifts&key=' . $key;
}

function get_ids_from_array($array) {
  return $array["id"];
}

function make_select($items, $selected, $name, $title = null) {
  $html_items = [];
  if (isset($title)) {
    $html_items[] = '<h4>' . $title . '</h4>' . "\n";
  }
  
  foreach ($items as $i) {
    $html_items[] = '<div class="checkbox"><label><input type="checkbox" name="' . $name . '[]" value="' . $i['id'] . '"' . (in_array($i['id'], $selected) ? ' checked="checked"' : '') . '> ' . $i['name'] . '</label>' . (! isset($i['enabled']) || $i['enabled'] ? '' : glyph("lock")) . '</div><br />';
  }
  $html = '<div id="selection_' . $name . '" class="selection ' . $name . '">' . "\n";
  $html .= implode("\n", $html_items);
  $html .= buttons([
      button("javascript: checkAll('selection_" . $name . "', true)", _("All"), ""),
      button("javascript: checkAll('selection_" . $name . "', false)", _("None"), "") 
  ]);
  $html .= '</div>' . "\n";
  return $html;
}
?>
