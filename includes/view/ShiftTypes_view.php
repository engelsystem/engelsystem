<?php

function ShiftType_delete_view($shifttype) {
}

function ShiftType_edit_view($name, $angeltype_id, $angeltypes, $description, $shifttype_id) {
}

function ShiftType_view($shifttype) {
}

function ShiftTypes_list_view($shifttypes) {
  foreach ($shifttypes as &$shifttype) {
    $shifttype['actions'] = table_buttons([
        button(page_link_to('shifttypes') . '&action=edit&shifttype_id=' . $shifttype['id'], _("edit"), "btn-xs"),
        button(page_link_to('shifttypes') . '&action=delete&shifttype_id=' . $shifttypes['id'], _("delete"), "btn-xs") 
    ]);
  }
  
  return page_with_title(shifttypes_title(), array(
      msg(),
      buttons(array(
          button(page_link_to('shifttypes') . '&action=edit', _("New shifttype"), 'add') 
      )),
      table(array(
          'name' => _("Name"),
          'actions' => "" 
      ), $shifttypes) 
  ));
}

?>