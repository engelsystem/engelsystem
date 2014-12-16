<?php

function shifttype_delete_controller() {
}

function shifttype_edit_controller() {
}

function shifttype_controller() {
}

function shifttypes_list_controller() {
}

/**
 * Text for shift type related links.
 */
function shifttypes_title() {
  return _("Shifttypes");
}

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