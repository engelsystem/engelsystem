<?php
function admin_arrive_title() {
  return _("Arrived angels");
}

function admin_arrive() {
  $msg = "";
  $search = "";
  if (isset($_REQUEST['search']))
    $search = strip_request_item('search');
  
  if (isset($_REQUEST['reset']) && preg_match("/^[0-9]*$/", $_REQUEST['reset'])) {
    $id = $_REQUEST['reset'];
    $user_source = User($id);
    if ($user_source != null) {
      sql_query("UPDATE `User` SET `Gekommen`=0 WHERE `UID`='" . sql_escape($id) . "' LIMIT 1");
      engelsystem_log("User set to not arrived: " . User_Nick_render($user_source));
      $msg = success(_("Reset done. Angel has not arrived."), true);
    } else
      $msg = error(_("Angel not found."), true);
  } elseif (isset($_REQUEST['arrived']) && preg_match("/^[0-9]*$/", $_REQUEST['arrived'])) {
    $id = $_REQUEST['arrived'];
    $user_source = User($id);
    if ($user_source != null) {
      sql_query("UPDATE `User` SET `Gekommen`=1 WHERE `UID`='" . sql_escape($id) . "' LIMIT 1");
      engelsystem_log("User set has arrived: " . User_Nick_render($user_source));
      $msg = success(_("Angel has been marked as arrived."), true);
    } else
      $msg = error(_("Angel not found."), true);
  }
  
  $users = sql_select("SELECT * FROM `User` ORDER BY `Nick`");
  $table = "";
  $users_matched = array();
  if ($search == "")
    $tokens = array();
  else
    $tokens = explode(" ", $search);
  foreach ($users as $usr) {
    if (count($tokens) > 0) {
      $match = false;
      $index = join(" ", $usr);
      foreach ($tokens as $t)
        if (stristr($index, trim($t))) {
          $match = true;
          break;
        }
      if (! $match)
        continue;
    }
    $table .= '<tr>';
    $table .= '<td>' . User_Nick_render($usr) . '</td>';
    $usr['nick'] = User_Nick_render($usr);
    $usr['arrived'] = $usr['Gekommen'] == 1 ? _("yes") : "";
    $usr['actions'] = $usr['Gekommen'] == 1 ? '<a href="' . page_link_to('admin_arrive') . '&reset=' . $usr['UID'] . '&search=' . $search . '">' . _("reset") . '</a>' : '<a href="' . page_link_to('admin_arrive') . '&arrived=' . $usr['UID'] . '&search=' . $search . '">' . _("arrived") . '</a>';
    if ($usr['Gekommen'] == 1)
      $table .= '<td>yes</td><td><a href="' . page_link_to('admin_arrive') . '&reset=' . $usr['UID'] . '&search=' . $search . '">reset</a></td>';
    else
      $table .= '<td></td><td><a href="' . page_link_to('admin_arrive') . '&arrived=' . $usr['UID'] . '&search=' . $search . '">arrived</a></td>';
    $table .= '</tr>';
    $users_matched[] = $usr;
  }
  return page_with_title(admin_arrive_title(), array(
      msg(),
      form(array(
          form_text('search', _("Search"), $search),
          form_submit('submit', _("Search")) 
      )),
      table(array(
          'nick' => _("Nickname"),
          'arrived' => _("Arrived?"),
          'actions' => "" 
      ), $users_matched) 
  ));
}
?>
