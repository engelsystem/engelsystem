<?php

function ShiftType_delete_view($shifttype) {
}

function ShiftType_edit_view($name, $angeltype_id, $angeltypes, $description, $shifttype_id) {
  $angeltypes_select = [
      '' => _('All') 
  ];
  foreach ($angeltypes as $angeltype)
    $angeltypes_select[$angeltype['id']] = $angeltype['name'];
  
  return page_with_title($shifttype_id ? _('Edit shifttype') : _('Create shifttype'), [
      msg(),
      buttons([
          button(page_link_to('shifttypes'), shifttypes_title(), 'back') 
      ]),
      form([
          form_text('name', _('Name'), $name),
          form_select('angeltype_id', _('Angeltype'), $angeltypes_select, $angeltype_id),
          form_textarea('description', _('Description'), $description),
          form_info('', _('Please use markdown for the description.')),
          form_submit('submit', _('Save')) 
      ]) 
  ]);
}

function ShiftType_view($shifttype) {
}

function ShiftTypes_list_view($shifttypes) {
  foreach ($shifttypes as &$shifttype) {
    $shifttype['actions'] = table_buttons([
        button(page_link_to('shifttypes') . '&action=edit&shifttype_id=' . $shifttype['id'], _('edit'), 'btn-xs'),
        button(page_link_to('shifttypes') . '&action=delete&shifttype_id=' . $shifttype['id'], _('delete'), 'btn-xs') 
    ]);
  }
  
  return page_with_title(shifttypes_title(), [
      msg(),
      buttons([
          button(page_link_to('shifttypes') . '&action=edit', _('New shifttype'), 'add') 
      ]),
      table([
          'name' => _('Name'),
          'actions' => '' 
      ], $shifttypes) 
  ]);
}

?>