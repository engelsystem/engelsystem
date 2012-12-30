<?php
function admin_active() {
  global $tshirt_sizes, $shift_sum_formula;

  $msg = "";
  $search = "";
  $count = 0;
  $limit = "";
  $set_active = "";
  if (isset ($_REQUEST['search']))
    $search = strip_request_item('search');

  if (isset ($_REQUEST['set_active'])) {
    $ok = true;

    if (isset ($_REQUEST['count']) && preg_match("/^[0-9]+$/", $_REQUEST['count']))
      $count = strip_request_item('count');
    else {
      $ok = false;
      $msg .= error("Please enter a number of angels to be marked as active.", true);
    }

    if ($ok)
      $limit = " LIMIT " . $count;
    if (isset ($_REQUEST['ack'])) {
      sql_query("UPDATE `User` SET `Aktiv` = 0 WHERE `Tshirt` = 0");
      $users = sql_select("SELECT `User`.*, COUNT(`ShiftEntry`.`id`) as `shift_count`, ${shift_sum_formula} as `shift_length` FROM `User` LEFT JOIN `ShiftEntry` ON `User`.`UID` = `ShiftEntry`.`UID` LEFT JOIN `Shifts` ON `ShiftEntry`.`SID` = `Shifts`.`SID` WHERE `User`.`Gekommen` = 1 GROUP BY `User`.`UID` ORDER BY `shift_length` DESC" . $limit);
      $user_nicks = array();
      foreach ($users as $usr) {
        sql_query("UPDATE `User` SET `Aktiv` = 1 WHERE `UID`=" . sql_escape($usr['UID']));
        $user_nicks[] = User_Nick_render($usr);
      }
      engelsystem_log("These angels are active now: " . join(", ", $user_nicks));

      $limit = "";
      $msg = success("Marked angels.", true);
    } else {
      $set_active = '<a href="' . page_link_to('admin_active') . '&amp;serach=' . $search . '">&laquo; back</a> | <a href="' . page_link_to('admin_active') . '&amp;search=' . $search . '&amp;count=' . $count . '&amp;set_active&amp;ack">apply</a>';
    }
  }

  if (isset ($_REQUEST['active']) && preg_match("/^[0-9]+$/", $_REQUEST['active'])) {
    $id = $_REQUEST['active'];
    $user_source = User($id);
    if($user_source != null) {
      sql_query("UPDATE `User` SET `Aktiv`=1 WHERE `UID`=" . sql_escape($id) . " LIMIT 1");
      engelsystem_log("User " . User_Nick_render($user_source) . " is active now.");
      $msg = success("Angel has been marked as active.", true);
    }
    else $msg = error("Angel not found.", true);
  }
  elseif (isset ($_REQUEST['not_active']) && preg_match("/^[0-9]+$/", $_REQUEST['not_active'])) {
    $id = $_REQUEST['not_active'];
    $user_source = User($id);
    if($user_source != null) {
      sql_query("UPDATE `User` SET `Aktiv`=0 WHERE `UID`=" . sql_escape($id) . " LIMIT 1");
      engelsystem_log("User " . User_Nick_render($user_source) . " is NOT active now.");
      $msg = success("Angel has been marked as not active.", true);
    }
    else $msg = error("Angel not found.", true);
  }
  elseif (isset ($_REQUEST['tshirt']) && preg_match("/^[0-9]+$/", $_REQUEST['tshirt'])) {
    $id = $_REQUEST['tshirt'];
    $user_source = User($id);
    if($user_source != null) {
      sql_query("UPDATE `User` SET `Tshirt`=1 WHERE `UID`=" . sql_escape($id) . " LIMIT 1");
      engelsystem_log("User " . User_Nick_render($user_source) . " has tshirt now.");
      $msg = success("Angel has got a t-shirt.", true);
    }
    else $msg = error("Angel not found.", true);
  }
  elseif (isset ($_REQUEST['not_tshirt']) && preg_match("/^[0-9]+$/", $_REQUEST['not_tshirt'])) {
    $id = $_REQUEST['not_tshirt'];
    $user_source = User($id);
    if($user_source != null) {
      sql_query("UPDATE `User` SET `Tshirt`=0 WHERE `UID`=" . sql_escape($id) . " LIMIT 1");
      engelsystem_log("User " . User_Nick_render($user_source) . " has NO tshirt.");
      $msg = success("Angel has got no t-shirt.", true);
    }
    else $msg = error("Angel not found.", true);
  }

  $users = sql_select("SELECT `User`.*, COUNT(`ShiftEntry`.`id`) as `shift_count`, ${shift_sum_formula} as `shift_length` FROM `User` LEFT JOIN `ShiftEntry` ON `User`.`UID` = `ShiftEntry`.`UID` LEFT JOIN `Shifts` ON `ShiftEntry`.`SID` = `Shifts`.`SID` WHERE `User`.`Gekommen` = 1 GROUP BY `User`.`UID` ORDER BY `shift_length` DESC" . $limit);

  $table = "";
  if ($search == "")
    $tokens = array ();
  else
    $tokens = explode(" ", $search);
  foreach ($users as $usr) {
    if (count($tokens) > 0) {
      $match = false;
      $index = join("", $usr);
      foreach ($tokens as $t)
        if (stristr($index, trim($t))) {
        $match = true;
        break;
      }
      if (!$match)
        continue;
    }
    $table .= '<tr>';
    $table .= '<td>' . User_Nick_render($usr) . '</td>';
    $table .= '<td>' . $tshirt_sizes[$usr['Size']] . '</td>';
    $table .= '<td>' . $usr['shift_count'] . '</td>';

    if ($usr['shift_count'] == 0)
      $table .= '<td>-</td>';
    else
      $table .= '<td>' . round($usr['shift_length'] / 60) . ' min (' . round($usr['shift_length'] / 3600) . ' h)</td>';

    if ($usr['Aktiv'] == 1)
      $table .= '<td>yes</td>';
    else
      $table .= '<td></td>';
    if ($usr['Tshirt'] == 1)
      $table .= '<td>yes</td>';
    else
      $table .= '<td></td>';

    $actions = array ();
    if ($usr['Aktiv'] == 0)
      $actions[] = '<a href="' . page_link_to('admin_active') . '&amp;active=' . $usr['UID'] . '&amp;search=' . $search . '">set active</a>';
    if ($usr['Aktiv'] == 1 && $usr['Tshirt'] == 0) {
      $actions[] = '<a href="' . page_link_to('admin_active') . '&amp;not_active=' . $usr['UID'] . '&amp;search=' . $search . '">remove active</a>';
      $actions[] = '<a href="' . page_link_to('admin_active') . '&amp;tshirt=' . $usr['UID'] . '&amp;search=' . $search . '">got t-shirt</a>';
    }
    if ($usr['Tshirt'] == 1)
      $actions[] = '<a href="' . page_link_to('admin_active') . '&amp;not_tshirt=' . $usr['UID'] . '&amp;search=' . $search . '">remove t-shirt</a>';

    $table .= '<td>' . join(' | ', $actions) . '</td>';

    $table .= '</tr>';
  }
  return template_render('../templates/admin_active.html', array (
    'search' => $search,
    'count' => $count,
    'set_active' => $set_active,
    'table' => $table,
    'msg' => $msg,
    'link' => page_link_to('admin_active')
  ));
}
?>
