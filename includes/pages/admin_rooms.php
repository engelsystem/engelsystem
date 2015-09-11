<?php

function admin_rooms_title() {
  return _("Locations");
}

function admin_rooms() {
  global $user;
  
  global $user, $enable_frab_import;
  $rooms_source = sql_select("SELECT * FROM `Room` ORDER BY `Name`");
  $rooms = array();
  foreach ($rooms_source as $room)
    $rooms[] = array(
        'name' => $room['Name'],
        'from_pentabarf' => $room['FromPentabarf'] == 'Y' ? '&#10003;' : '',
        'public' => $room['show'] == 'Y' ? '&#10003;' : '',
        'actions' => buttons(array(
            button(page_link_to('admin_rooms') . '&show=edit&id=' . $room['RID'], _("edit"), 'btn-xs'),
            button(page_link_to('admin_rooms') . '&show=delete&id=' . $room['RID'], _("delete"), 'btn-xs') 
        )) 
    );
  
  if (isset($_REQUEST['show'])) {
    $msg = "";
    $name = "";
    $from_pentabarf = "";
    $public = 'Y';
    $number = "";
    
    $angeltypes_source = sql_select("SELECT * FROM `AngelTypes` ORDER BY `name`");
    $angeltypes = array();
    $angeltypes_count = array();
    foreach ($angeltypes_source as $angeltype) {
      $angeltypes[$angeltype['id']] = $angeltype['name'];
      $angeltypes_count[$angeltype['id']] = 0;
    }
    
    if (test_request_int('id')) {
      $room = sql_select("SELECT * FROM `Room` WHERE `RID`='" . sql_escape($_REQUEST['id']) . "'");
      if (count($room) > 0) {
        $id = $_REQUEST['id'];
        $name = $room[0]['Name'];
        $from_pentabarf = $room[0]['FromPentabarf'];
        $public = $room[0]['show'];
        $needed_angeltypes = sql_select("SELECT * FROM `NeededAngelTypes` WHERE `room_id`='" . sql_escape($id) . "'");
        foreach ($needed_angeltypes as $needed_angeltype)
          $angeltypes_count[$needed_angeltype['angel_type_id']] = $needed_angeltype['count'];
      } else
        redirect(page_link_to('admin_rooms'));
    }
    
    if ($_REQUEST['show'] == 'edit') {
      if (isset($_REQUEST['submit'])) {
        $ok = true;
        
        if (isset($_REQUEST['name']) && strlen(strip_request_item('name')) > 0)
          $name = strip_request_item('name');
        else {
          $ok = false;
          $msg .= error(_("Please enter a name."), true);
        }
        
        if (isset($_REQUEST['from_pentabarf']))
          $from_pentabarf = 'Y';
        else
          $from_pentabarf = '';
        
        if (isset($_REQUEST['public']))
          $public = 'Y';
        else
          $public = '';
        
        if (isset($_REQUEST['number']))
          $number = strip_request_item('number');
        else
          $ok = false;
        
        foreach ($angeltypes as $angeltype_id => $angeltype) {
          if (isset($_REQUEST['angeltype_count_' . $angeltype_id]) && preg_match("/^[0-9]{1,4}$/", $_REQUEST['angeltype_count_' . $angeltype_id]))
            $angeltypes_count[$angeltype_id] = $_REQUEST['angeltype_count_' . $angeltype_id];
          else {
            $ok = false;
            $msg .= error(sprintf(_("Please enter needed angels for type %s.", $angeltype)), true);
          }
        }
        
        if ($ok) {
          if (isset($id)) {
            sql_query("UPDATE `Room` SET `Name`='" . sql_escape($name) . "', `FromPentabarf`='" . sql_escape($from_pentabarf) . "', `show`='" . sql_escape($public) . "', `Number`='" . sql_escape($number) . "' WHERE `RID`='" . sql_escape($id) . "' LIMIT 1");
            engelsystem_log("Location updated: " . $name . ", pentabarf import: " . $from_pentabarf . ", public: " . $public . ", number: " . $number);
          } else {
            $id = Room_create($name, $from_pentabarf, $public, $number);
            if ($id === false)
              engelsystem_error("Unable to create location.");
            engelsystem_log("Location created: " . $name . ", pentabarf import: " . $from_pentabarf . ", public: " . $public . ", number: " . $number);
          }
          
          sql_query("DELETE FROM `NeededAngelTypes` WHERE `room_id`='" . sql_escape($id) . "'");
          $needed_angeltype_info = array();
          foreach ($angeltypes_count as $angeltype_id => $angeltype_count) {
            $angeltype = AngelType($angeltype_id);
            if ($angeltype === false)
              engelsystem_error("Unable to load angeltype.");
            if ($angeltype != null) {
              sql_query("INSERT INTO `NeededAngelTypes` SET `room_id`='" . sql_escape($id) . "', `angel_type_id`='" . sql_escape($angeltype_id) . "', `count`='" . sql_escape($angeltype_count) . "'");
              $needed_angeltype_info[] = $angeltype['name'] . ": " . $angeltype_count;
            }
          }
          
          engelsystem_log("Set needed angeltypes of location " . $name . " to: " . join(", ", $needed_angeltype_info));
          success(_("Location saved."));
          redirect(page_link_to("admin_rooms"));
        }
      }
      $angeltypes_count_form = array();
      foreach ($angeltypes as $angeltype_id => $angeltype)
        $angeltypes_count_form[] = div('col-lg-4 col-md-6 col-xs-6', array(
            form_spinner('angeltype_count_' . $angeltype_id, $angeltype, $angeltypes_count[$angeltype_id]) 
        ));
      

      $form_elements = [];
      $form_elements[] = form_text('name', _("Name"), $name);
      if ($enable_frab_import) {
        $form_elements[] = form_checkbox('from_pentabarf', _("Frab import"), $from_pentabarf);
      }
      $form_elements[] = form_checkbox('public', _("Public"), $public);
      $form_elements[] = form_text('number', _("Room number"), $number);

      return page_with_title(admin_rooms_title(), array(
          buttons(array(
              button(page_link_to('admin_rooms'), _("back"), 'back') 
          )),
          $msg,
          form(array(
              div('row', array(
                  div('col-md-6', $form_elements),
                  div('col-md-6', array(
                      div('row', array(
                          div('col-md-12', array(
                              form_info(_("Needed angels:")) 
                          )),
                          join($angeltypes_count_form) 
                      )) 
                  )) 
              )),
              form_submit('submit', _("Save")) 
          )) 
      ));
    } elseif ($_REQUEST['show'] == 'delete') {
      if (isset($_REQUEST['ack'])) {
        sql_query("DELETE FROM `Room` WHERE `RID`='" . sql_escape($id) . "' LIMIT 1");
        sql_query("DELETE FROM `NeededAngelTypes` WHERE `room_id`='" . sql_escape($id) . "' LIMIT 1");
        
        engelsystem_log("Location deleted: " . $name);
        success(sprintf(_("Location %s deleted."), $name));
        redirect(page_link_to('admin_rooms'));
      }
      
      return page_with_title(admin_rooms_title(), array(
          buttons(array(
              button(page_link_to('admin_rooms'), _("back"), 'back') 
          )),
          sprintf(_("Do you want to delete location %s?"), $name),
          buttons(array(
              button(page_link_to('admin_rooms') . '&show=delete&id=' . $id . '&ack', _("Delete"), 'delete') 
          )) 
      ));
    }
  }
  

  $table_columns = array(
    'name' => _("Name"),
    'from_pentabarf' => _("Frab import"),
    'public' => _("Public"),
    'actions' => ""
  );
  if (!$enable_frab_import) {
    unset($table_columns['from_pentabarf']);
  }

  return page_with_title(admin_rooms_title(), array(
      buttons(array(
          button(page_link_to('admin_rooms') . '&show=edit', _("add")) 
      )),
      msg(),
      table($table_columns, $rooms)
  ));
}
?>
