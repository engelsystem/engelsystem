<?php

/**
 * Text for Angeltype related links.
 */
function angeltypes_title() {
  return _("Angeltypes");
}

/**
 * Route angeltype actions.
 */
function angeltypes_controller() {
  $action = strip_request_item('action', 'list');
  
  switch ($action) {
    default:
    case 'list':
      return angeltypes_list_controller();
    case 'view':
      return angeltype_controller();
    case 'edit':
      return angeltype_edit_controller();
    case 'delete':
      return angeltype_delete_controller();
    case 'about':
      return angeltypes_about_controller();
  }
}

/**
 * Path to angeltype view.
 *
 * @param AngelType $angeltype_id          
 */
function angeltype_link($angeltype_id) {
  return page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype_id;
}

/**
 * Job description for all angeltypes (public to everyone)
 */
function angeltypes_about_controller() {
  global $user;
  
  if (isset($user)) {
    $angeltypes = AngelTypes_with_user($user);
  } else {
    $angeltypes = AngelTypes();
  }
  
  return [
      _("Teams/Job description"),
      AngelTypes_about_view($angeltypes, isset($user)) 
  ];
}

/**
 * Delete an Angeltype.
 */
function angeltype_delete_controller() {
  global $privileges;
  
  if (! in_array('admin_angel_types', $privileges)) {
    redirect(page_link_to('angeltypes'));
  }
  
  $angeltype = load_angeltype();
  
  if (isset($_REQUEST['confirmed'])) {
    AngelType_delete($angeltype);
    success(sprintf(_("Angeltype %s deleted."), AngelType_name_render($angeltype)));
    redirect(page_link_to('angeltypes'));
  }
  
  return [
      sprintf(_("Delete angeltype %s"), $angeltype['name']),
      AngelType_delete_view($angeltype) 
  ];
}

/**
 * Change an Angeltype.
 */
function angeltype_edit_controller() {
  global $privileges, $user;
  
  // In supporter mode only allow to modify description
  $supporter_mode = ! in_array('admin_angel_types', $privileges);
  
  if (isset($_REQUEST['angeltype_id'])) {
    // Edit existing angeltype
    $angeltype = load_angeltype();
    
    if (! User_is_AngelType_supporter($user, $angeltype)) {
      redirect(page_link_to('angeltypes'));
    }
  } else {
    // New angeltype
    if ($supporter_mode) {
      // Supporters aren't allowed to create new angeltypes.
      redirect(page_link_to('angeltypes'));
    }
    $angeltype = AngelType_new();
  }
  
  if (isset($_REQUEST['submit'])) {
    $valid = true;
    
    if (! $supporter_mode) {
      if (isset($_REQUEST['name'])) {
        $result = AngelType_validate_name($_REQUEST['name'], $angeltype);
        $angeltype['name'] = $result->getValue();
        if (! $result->isValid()) {
          $valid = false;
          error(_("Please check the name. Maybe it already exists."));
        }
      }
      
      $angeltype['restricted'] = isset($_REQUEST['restricted']);
      $angeltype['requires_driver_license'] = isset($_REQUEST['requires_driver_license']);
    }
    
    $angeltype['description'] = strip_request_item_nl('description', $angeltype['description']);
    
    if ($valid) {
      if ($angeltype['id'] != null) {
        AngelType_update($angeltype);
      } else {
        $angeltype = AngelType_create($angeltype);
      }
      
      success("Angel type saved.");
      redirect(angeltype_link($angeltype['id']));
    }
  }
  
  return [
      sprintf(_("Edit %s"), $angeltype['name']),
      AngelType_edit_view($angeltype, $supporter_mode) 
  ];
}

/**
 * View details of a given angeltype.
 */
function angeltype_controller() {
  global $privileges, $user;
  
  if (! in_array('angeltypes', $privileges)) {
    redirect('?');
  }
  
  $angeltype = load_angeltype();
  $user_angeltype = UserAngelType_by_User_and_AngelType($user, $angeltype);
  $user_driver_license = UserDriverLicense($user['UID']);
  $members = Users_by_angeltype($angeltype);
  
  return [
      sprintf(_("Team %s"), $angeltype['name']),
      AngelType_view($angeltype, $members, $user_angeltype, in_array('admin_user_angeltypes', $privileges) || $user_angeltype['supporter'], in_array('admin_angel_types', $privileges), $user_angeltype['supporter'], $user_driver_license, $user) 
  ];
}

/**
 * View a list of all angeltypes.
 */
function angeltypes_list_controller() {
  global $privileges, $user;
  
  if (! in_array('angeltypes', $privileges)) {
    redirect('?');
  }
  
  $angeltypes = AngelTypes_with_user($user);
  
  foreach ($angeltypes as &$angeltype) {
    $actions = [
        button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], _("view"), "btn-xs") 
    ];
    
    if (in_array('admin_angel_types', $privileges)) {
      $actions[] = button(page_link_to('angeltypes') . '&action=edit&angeltype_id=' . $angeltype['id'], _("edit"), "btn-xs");
      $actions[] = button(page_link_to('angeltypes') . '&action=delete&angeltype_id=' . $angeltype['id'], _("delete"), "btn-xs");
    }
    
    $angeltype['membership'] = AngelType_render_membership($angeltype);
    if ($angeltype['user_angeltype_id'] != null) {
      $actions[] = button(page_link_to('user_angeltypes') . '&action=delete&user_angeltype_id=' . $angeltype['user_angeltype_id'], _("leave"), "btn-xs");
    } else {
      $actions[] = button(page_link_to('user_angeltypes') . '&action=add&angeltype_id=' . $angeltype['id'], _("join"), "btn-xs");
    }
    
    $angeltype['restricted'] = $angeltype['restricted'] ? glyph('lock') : '';
    $angeltype['name'] = '<a href="' . page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'] . '">' . $angeltype['name'] . '</a>';
    
    $angeltype['actions'] = table_buttons($actions);
  }
  
  return [
      angeltypes_title(),
      AngelTypes_list_view($angeltypes, in_array('admin_angel_types', $privileges)) 
  ];
}

/**
 * Loads an angeltype from given angeltype_id request param.
 */
function load_angeltype() {
  if (! isset($_REQUEST['angeltype_id'])) {
    redirect(page_link_to('angeltypes'));
  }
  
  $angeltype = AngelType($_REQUEST['angeltype_id']);
  if ($angeltype == null) {
    error(_("Angeltype doesn't exist."));
    redirect(page_link_to('angeltypes'));
  }
  
  return $angeltype;
}
?>
