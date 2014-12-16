<?php

function shifttype_delete_controller() {
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
      if ($shifttype_id) {} else {
        $shifttype_id = ShiftType_create($name, $angeltype_id, $description);
        if ($shifttype_id === false)
          engelsystem_error('Unable to create shift type.');
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