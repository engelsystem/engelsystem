<?php
function admin_free_title() {
  return _("Free angels");
}

function admin_free() {
  global $privileges;
  
  $search = "";
  if (isset($_REQUEST['search']))
    $search = strip_request_item('search');
  
  $angeltypesearch = "";
  if (empty($_REQUEST['angeltype']))
    $_REQUEST['angeltype'] = '';
  else {
    $angeltypesearch = " INNER JOIN `UserAngelTypes` ON (`UserAngelTypes`.`angeltype_id` = '" . sql_escape($_REQUEST['angeltype']) . "' AND `UserAngelTypes`.`user_id` = `User`.`UID`";
    if (isset($_REQUEST['confirmed_only']))
      $angeltypesearch .= " AND `UserAngelTypes`.`confirm_user_id`";
    $angeltypesearch .= ") ";
  }
  
  $angel_types_source = sql_select("SELECT `id`, `name` FROM `AngelTypes` ORDER BY `name`");
  $angel_types = array(
      '' => 'alle Typen' 
  );
  foreach ($angel_types_source as $angel_type)
    $angel_types[$angel_type['id']] = $angel_type['name'];
  
  $users = sql_select("
      SELECT `User`.* 
      FROM `User` 
      ${angeltypesearch} 
      LEFT JOIN `ShiftEntry` ON `User`.`UID` = `ShiftEntry`.`UID` 
      LEFT JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID` AND `Shifts`.`start` < '" . sql_escape(time()) . "' AND `Shifts`.`end` > '" . sql_escape(time()) . "') 
      WHERE `User`.`Gekommen` = 1 AND `Shifts`.`SID` IS NULL 
      GROUP BY `User`.`UID` 
      ORDER BY `Nick`");
  
  $free_users_table = array();
  if ($search == "")
    $tokens = array();
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
      if (! $match)
        continue;
    }
    
    $free_users_table[] = array(
        'name' => User_Nick_render($usr),
        'shift_state' => User_shift_state_render($usr),
        'handy' => $usr['Handy'],
        'telefon' => $usr['Telefon'],
        'email' => $usr['email'],
        'actions' => in_array('admin_user', $privileges) ? button(page_link_to('admin_user') . '&amp;id=' . $usr['UID'], _("edit"), 'btn-xs') : '' 
    );
  }
  return page_with_title(admin_free_title(), array(
      form(array(
          div('row', array(
              div('col-md-4', array(
                  form_text('search', _("Search"), $search) 
              )),
              div('col-md-4', array(
                  form_select('angeltype', _("Angeltype"), $angel_types, $_REQUEST['angeltype']) 
              )),
              div('col-md-2', array(
                  form_checkbox('confirmed_only', _("Only confirmed"), isset($_REQUEST['confirmed_only'])) 
              )),
              div('col-md-2', array(
                  form_submit('submit', _("Search")) 
              )) 
          )) 
      )),
      table(array(
          'name' => _("Nick"),
          'shift_state' => '',
          'handy' => _("Mobile"),
          'telefon' => _("Phone"),
          'email' => _("E-Mail"),
          'actions' => '' 
      ), $free_users_table) 
  ));
}
?>
