<?php
use Engelsystem\ShiftsFilter;

function shifts_title() {
  return _("Shifts");
}

/**
 * Start different controllers for deleting shifts and shift_entries, edit shifts and add shift entries.
 */
function user_shifts() {
  global $user;
  
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

/**
 * Helper function that updates the start and end time from request data.
 * Use update_ShiftsFilter().
 *
 * @param ShiftsFilter $shiftsFilter
 *          The shiftfilter to update.
 */
function update_ShiftsFilter_timerange(ShiftsFilter $shiftsFilter) {
  $day = date('Y-m-d', time());
  $start_day = in_array($day, $days) ? $day : min($days);
  if (isset($_REQUEST['start_day']) && in_array($_REQUEST['start_day'], $days)) {
    $start_day = $_REQUEST['start_day'];
  }
  
  $start_time = date("H:i");
  if (isset($_REQUEST['start_time']) && preg_match('#^\d{1,2}:\d\d$#', $_REQUEST['start_time'])) {
    $start_time = $_REQUEST['start_time'];
  }
  
  $day = date('Y-m-d', time() + 24 * 60 * 60);
  $end_day = in_array($day, $days) ? $day : max($days);
  if (isset($_REQUEST['end_day']) && in_array($_REQUEST['end_day'], $days)) {
    $end_day = $_REQUEST['end_day'];
  }
  
  $end_time = date("H:i");
  if (isset($_REQUEST['end_time']) && preg_match('#^\d{1,2}:\d\d$#', $_REQUEST['end_time'])) {
    $end_time = $_REQUEST['end_time'];
  }
  
  if ($start_day > $end_day) {
    $end_day = $start_day;
  }
  if ($start_day == $end_day && $start_time >= $end_time) {
    $end_time = "23:59";
  }
  
  $shiftsFilter->setStartTime(parse_date("Y-m-d H:i", $start_day . " " . $start_time));
  $shiftsFilter->setEndTime(parse_date("Y-m-d H:i", $end_day . " " . $end_time));
}

/**
 * Update given ShiftsFilter with filter params from user input
 *
 * @param ShiftsFilter $shiftsFilter
 *          The shifts filter to update from request data
 * @param boolean $user_shifts_admin
 *          Has the user user_shift_admin privilege?
 * @param string[] $days
 *          An array of available filter days
 */
function update_ShiftsFilter(ShiftsFilter $shiftsFilter, $user_shifts_admin, $days) {
  $shiftsFilter->setUserShiftsAdmin($user_shifts_admin);
  if (isset($_REQUEST['filled'])) {
    $shiftsFilter->setFilled(check_request_int_array('filled'));
  }
  if (isset($_REQUEST['rooms'])) {
    $shiftsFilter->setRooms(check_request_int_array('rooms'));
  }
  if (isset($_REQUEST['types'])) {
    $shiftsFilter->setTypes(check_request_int_array('types'));
  }
  if ((isset($_REQUEST['start_time']) && isset($_REQUEST['start_day']) && isset($_REQUEST['end_time']) && isset($_REQUEST['end_day'])) || $shiftsFilter->getStartTime() == null || $shiftsFilter->getEndTime() == null) {
    update_ShiftsFilter_timerange($shiftsFilter);
  }
}

function load_rooms() {
  $rooms = sql_select("SELECT `RID` AS `id`, `Name` AS `name` FROM `Room` WHERE `show`='Y' ORDER BY `Name`");
  if (count($rooms) == 0) {
    error(_("The administration has not configured any rooms yet."));
    redirect('?');
  }
  return $rooms;
}

function load_days() {
  $days = sql_select_single_col("
      SELECT DISTINCT DATE(FROM_UNIXTIME(`start`)) AS `id`, DATE(FROM_UNIXTIME(`start`)) AS `name`
      FROM `Shifts`
      ORDER BY `start`");
  if (count($days) == 0) {
    error(_("The administration has not configured any shifts yet."));
    redirect('?');
  }
  return $days;
}

function load_types() {
  global $user;
  
  if (sql_num_query("SELECT `id`, `name` FROM `AngelTypes` WHERE `restricted` = 0") == 0) {
    error(_("The administration has not configured any angeltypes yet - or you are not subscribed to any angeltype."));
    redirect('?');
  }
  $types = sql_select("SELECT `AngelTypes`.`id`, `AngelTypes`.`name`, (`AngelTypes`.`restricted`=0 OR (NOT `UserAngelTypes`.`confirm_user_id` IS NULL OR `UserAngelTypes`.`id` IS NULL)) as `enabled` FROM `AngelTypes` LEFT JOIN `UserAngelTypes` ON (`UserAngelTypes`.`angeltype_id`=`AngelTypes`.`id` AND `UserAngelTypes`.`user_id`='" . sql_escape($user['UID']) . "') ORDER BY `AngelTypes`.`name`");
  if (empty($types)) {
    return sql_select("SELECT `id`, `name` FROM `AngelTypes` WHERE `restricted` = 0");
  }
  return $types;
}

function view_user_shifts() {
  global $user, $privileges;
  global $ical_shifts;
  
  $ical_shifts = [];
  $days = load_days();
  $rooms = load_rooms();
  $types = load_types();
  
  if (! isset($_SESSION['ShiftsFilter'])) {
    $room_ids = array_map('get_ids_from_array', $rooms);
    $type_ids = array_map('get_ids_from_array', $types);
    $_SESSION['ShiftsFilter'] = new ShiftsFilter(in_array('user_shifts_admin', $privileges), $room_ids, $type_ids);
  }
  update_ShiftsFilter($_SESSION['ShiftsFilter'], in_array('user_shifts_admin', $privileges), $days);
  $shiftsFilter = $_SESSION['ShiftsFilter'];
  
  $shifts = Shifts_by_ShiftsFilter($shiftsFilter, $user);
  
  $ownshifts_source = sql_select("
      SELECT `ShiftTypes`.`name`, `Shifts`.*
      FROM `Shifts`
      INNER JOIN `ShiftTypes` ON (`ShiftTypes`.`id` = `Shifts`.`shifttype_id`)
      INNER JOIN `ShiftEntry` ON (`Shifts`.`SID` = `ShiftEntry`.`SID` AND `ShiftEntry`.`UID` = '" . sql_escape($user['UID']) . "')
      WHERE `Shifts`.`RID` IN (" . implode(',', $shiftsFilter->getRooms()) . ")
      AND `start` BETWEEN " . $shiftsFilter->getStartTime() . " AND " . $shiftsFilter->getEndTime());
  $ownshifts = [];
  foreach ($ownshifts_source as $ownshift) {
    $ownshifts[$ownshift['SID']] = $ownshift;
  }
  unset($ownshifts_source);
  
  $shifts_table = "";
  /*
   * [0] => Array ( [SID] => 1 [start] => 1355958000 [end] => 1355961600 [RID] => 1 [name] => [URL] => [PSID] => [room_name] => test1 [has_special_needs] => 1 [is_full] => 0 )
   */
  $first = 15 * 60 * floor($shiftsFilter->getStartTime() / (15 * 60));
  $maxshow = ceil(($shiftsFilter->getEndTime() - $first) / (60 * 15));
  $block = [];
  $todo = [];
  $myrooms = $rooms;
  
  // delete un-selected rooms from array
  foreach ($myrooms as $k => $v) {
    if (array_search($v["id"], $shiftsFilter->getRooms()) === false) {
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
    if ($thistime % (24 * 60 * 60) == 23 * 60 * 60 && $shiftsFilter->getEndTime() - $shiftsFilter->getStartTime() > 24 * 60 * 60) {
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
            if (! empty($shiftsFilter->getTypes())) {
              $query .= " AND `angel_type_id` IN (" . implode(',', $shiftsFilter->getTypes()) . ") ";
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
            // also output that shift on ical export
            $ical_shifts[] = $shift;
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
  
  if ($user['api_key'] == "") {
    User_reset_api_key($user, false);
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
  $start_day = date("Y-m-d", $shiftsFilter->getStartTime());
  $start_time = date("H:i", $shiftsFilter->getStartTime());
  $end_day = date("Y-m-d", $shiftsFilter->getEndTime());
  $end_time = date("H:i", $shiftsFilter->getEndTime());
  
  return page([
      div('col-md-12', [
          msg(),
          template_render('../templates/user_shifts.html', [
              'title' => shifts_title(),
              'room_select' => make_select($rooms, $shiftsFilter->getRooms(), "rooms", _("Rooms")),
              'start_select' => html_select_key("start_day", "start_day", array_combine($days, $days), $start_day),
              'start_time' => $start_time,
              'end_select' => html_select_key("end_day", "end_day", array_combine($days, $days), $end_day),
              'end_time' => $end_time,
              'type_select' => make_select($types, $shiftsFilter->getTypes(), "types", _("Angeltypes") . '<sup>1</sup>'),
              'filled_select' => make_select($filled, $shiftsFilter->getFilled(), "filled", _("Occupancy")),
              'task_notice' => '<sup>1</sup>' . _("The tasks shown here are influenced by the angeltypes you joined already!") . " <a href=\"" . page_link_to('angeltypes') . '&action=about' . "\">" . _("Description of the jobs.") . "</a>",
              'shifts_table' => msg() . $shifts_table,
              'ical_text' => '<h2>' . _("iCal export") . '</h2><p>' . sprintf(_("Export of shown shifts. <a href=\"%s\">iCal format</a> or <a href=\"%s\">JSON format</a> available (please keep secret, otherwise <a href=\"%s\">reset the api key</a>)."), page_link_to_absolute('ical') . '&key=' . $user['api_key'], page_link_to_absolute('shifts_json_export') . '&key=' . $user['api_key'], page_link_to('user_myshifts') . '&reset') . '</p>',
              'filter' => _("Filter") 
          ]) 
      ]) 
  ]);
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
