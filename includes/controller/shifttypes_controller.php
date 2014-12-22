<?php

function shifttype_link($shifttype) {
  return page_link_to('shifttypes') . '&action=view&shifttype_id=' . $shifttype['id'];
}

/**
 * Delete a shifttype.
 */
function shifttype_delete_controller() {
  if (! isset($_REQUEST['shifttype_id']))
    redirect(page_link_to('shifttypes'));
  $shifttype = ShiftType($_REQUEST['shifttype_id']);
  if ($shifttype === false)
    engelsystem_error('Unable to load shifttype.');
  if ($shifttype == null)
    redirect(page_link_to('shifttypes'));
  
  if (isset($_REQUEST['confirmed'])) {
    $result = ShiftType_delete($shifttype['id']);
    if ($result === false)
      engelsystem_error('Unable to delete shifttype.');
    
    engelsystem_log('Deleted shifttype ' . $shifttype['name']);
    success(sprintf(_('Shifttype %s deleted.'), $shifttype['name']));
    redirect(page_link_to('shifttypes'));
  }
  
  return array(
      sprintf(_("Delete shifttype %s"), $shifttype['name']),
      ShiftType_delete_view($shifttype) 
  );
}

/**
 * Edit or create shift type.
 */
function shifttype_edit_controller() {
  $shifttype_id = null;
  $name = "";
  $angeltype_id = null;
  $description = "";
  
  $angeltypes = AngelTypes();
  if ($angeltypes === false)
    engelsystem_error("Unable to load angel types.");
  
  if (isset($_REQUEST['shifttype_id'])) {
    $shifttype = ShiftType($_REQUEST['shifttype_id']);
    if ($shifttype === false)
      engelsystem_error('Unable to load shifttype.');
    if ($shifttype == null) {
      error(_('Shifttype not found.'));
      redirect(page_link_to('shifttypes'));
    }
    $shifttype_id = $shifttype['id'];
    $name = $shifttype['name'];
    $angeltype_id = $shifttype['angeltype_id'];
    $description = $shifttype['description'];
  }
  
  if (isset($_REQUEST['submit'])) {
    $ok = true;
    
    if (isset($_REQUEST['name']) && $_REQUEST['name'] != '')
      $name = strip_request_item('name');
    else {
      $ok = false;
      error(_('Please enter a name.'));
    }
    
    if (isset($_REQUEST['angeltype_id']) && preg_match("/^[0-9]+$/", $_REQUEST['angeltype_id']))
      $angeltype_id = $_REQUEST['angeltype_id'];
    else
      $angeltype_id = null;
    
    if (isset($_REQUEST['description']))
      $description = strip_request_item_nl('description');
    
    if ($ok) {
      if ($shifttype_id) {
        $result = ShiftType_update($shifttype_id, $name, $angeltype_id, $description);
        if ($result === false)
          engelsystem_error('Unable to update shifttype.');
        engelsystem_log('Updated shifttype ' . $name);
        success(_('Updated shifttype.'));
      } else {
        $shifttype_id = ShiftType_create($name, $angeltype_id, $description);
        if ($shifttype_id === false)
          engelsystem_error('Unable to create shifttype.');
        engelsystem_log('Created shifttype ' . $name);
        success(_('Created shifttype.'));
      }
      redirect(page_link_to('shifttypes') . '&action=view&shifttype_id=' . $shifttype_id);
    }
  }
  
  return [
      shifttypes_title(),
      ShiftType_edit_view($name, $angeltype_id, $angeltypes, $description, $shifttype_id) 
  ];
}

function shifttype_controller() {
  if (! isset($_REQUEST['shifttype_id']))
    redirect(page_link_to('shifttypes'));
  $shifttype = ShiftType($_REQUEST['shifttype_id']);
  if ($shifttype === false)
    engelsystem_error('Unable to load shifttype.');
  if ($shifttype == null)
    redirect(page_link_to('shifttypes'));
  
  $angeltype = null;
  if ($shifttype['angeltype_id'] != null) {
    $angeltype = AngelType($shifttype['angeltype_id']);
    if ($angeltype === false)
      engelsystem_error('Unable to load angeltype.');
  }
  
  return [
      $shifttype['name'],
      ShiftType_view($shifttype, $angeltype) 
  ];
}

/**
 * List all shift types.
 */
function shifttypes_list_controller() {
  $shifttypes = ShiftTypes();
  if ($shifttypes === false)
    engelsystem_error("Unable to load shifttypes.");
  
  return [
      shifttypes_title(),
      ShiftTypes_list_view($shifttypes) 
  ];
}

/**
 * Text for shift type related links.
 */
function shifttypes_title() {
  return _("Shifttypes");
}

/**
 * Route shift type actions
 */
function shifttypes_controller() {
  if (! isset($_REQUEST['action']))
    $_REQUEST['action'] = 'list';
  
  switch ($_REQUEST['action']) {
    default:
    case 'list':
      return shifttypes_list_controller();
    case 'view':
      return shifttype_controller();
    case 'edit':
      return shifttype_edit_controller();
    case 'delete':
      return shifttype_delete_controller();
  }
}

?>