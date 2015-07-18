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
      sql_query("UPDATE `User` SET `Gekommen`=0, `arrival_date` = NULL WHERE `UID`='" . sql_escape($id) . "' LIMIT 1");
      engelsystem_log("User set to not arrived: " . User_Nick_render($user_source));
      $msg = success(_("Reset done. Angel has not arrived."), true);
    } else
      $msg = error(_("Angel not found."), true);
  } elseif (isset($_REQUEST['arrived']) && preg_match("/^[0-9]*$/", $_REQUEST['arrived'])) {
    $id = $_REQUEST['arrived'];
    $user_source = User($id);
    if ($user_source != null) {
      sql_query("UPDATE `User` SET `Gekommen`=1, `arrival_date`='" . time() . "' WHERE `UID`='" . sql_escape($id) . "' LIMIT 1");
      engelsystem_log("User set has arrived: " . User_Nick_render($user_source));
      $msg = success(_("Angel has been marked as arrived."), true);
    } else
      $msg = error(_("Angel not found."), true);
  }
  
  $users = sql_select("SELECT * FROM `User` ORDER BY `Nick`");
  $arrival_count_at_day = array();
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
    
    $usr['nick'] = User_Nick_render($usr);
    $usr['rendered_planned_arrival_date'] = date('Y-m-d', $usr['planned_arrival_date']);
    $usr['rendered_arrival_date'] = $usr['arrival_date'] > 0 ? date('Y-m-d', $usr['arrival_date']) : "-";
    $usr['arrived'] = $usr['Gekommen'] == 1 ? _("yes") : "";
    $usr['actions'] = $usr['Gekommen'] == 1 ? '<a href="' . page_link_to('admin_arrive') . '&reset=' . $usr['UID'] . '&search=' . $search . '">' . _("reset") . '</a>' : '<a href="' . page_link_to('admin_arrive') . '&arrived=' . $usr['UID'] . '&search=' . $search . '">' . _("arrived") . '</a>';
    
    $day = $usr['arrival_date'] > 0 ? date('Y-m-d', $usr['arrival_date']) : date('Y-m-d', $usr['planned_arrival_date']);
    if (! isset($arrival_count_at_day[$day]))
      $arrival_count_at_day[$day] = 0;
    $arrival_count_at_day[$day] ++;
    
    $users_matched[] = $usr;
  }
  
  ksort($arrival_count_at_day);
  
  $arrival_count = array();
  $arrival_sums = array();
  $arrival_sum = 0;
  foreach ($arrival_count_at_day as $day => $count) {
    $arrival_sum += $count;
    $arrival_sums[$day] = $arrival_sum;
    $arrival_count[] = array(
        'day' => $day,
        'count' => $count,
        'sum' => $arrival_sum 
    );
  }
  
  return page_with_title(admin_arrive_title(), array(
      msg(),
      form(array(
          form_text('search', _("Search"), $search),
          form_submit('submit', _("Search")) 
      )),
      table(array(
          'nick' => _("Nickname"),
          'rendered_planned_arrival_date' => _("Planned date"),
          'arrived' => _("Arrived?"),
          'rendered_arrival_date' => _("Arrival date"),
          'actions' => "" 
      ), $users_matched),
      heading(_("Arrival statistics"), 2),
      '<canvas id="daily_arrives" style="width: 100%; height: 300px;"></canvas>
      <script type="text/javascript">
      $(function(){
        var ctx = $("#daily_arrives").get(0).getContext("2d");
        var chart = new Chart(ctx).Bar(' . json_encode(array(
          'labels' => array_keys($arrival_count_at_day),
          'datasets' => array(
              array(
                  'label' => _("arrived"),
                  'fillColor' => "#444",
                  'data' => array_values($arrival_count_at_day) 
              ),
              array(
                  'label' => _("arrived sum"),
                  'fillColor' => "#888",
                  'data' => array_values($arrival_sums) 
              ) 
          ) 
      )) . ', {"responsive": true});
      });
      </script>',
      table(array(
          'day' => _("Date"),
          'count' => _("arrived"),
          'sum' => _("arrived sum") 
      ), $arrival_count) 
  ));
}
?>
