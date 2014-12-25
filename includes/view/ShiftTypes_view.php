<?php

function ShiftType_name_render($shifttype) {
  global $privileges;
  if (in_array('shifttypes', $privileges))
    return '<a href="' . shifttype_link($shifttype) . '">' . $shifttype['name'] . '</a>';
  return $shifttype['name'];
}

function ShiftType_delete_view($shifttype) {
  return page_with_title(sprintf(_("Delete shifttype %s"), $shifttype['name']), array(
      info(sprintf(_("Do you want to delete shifttype %s?"), $shifttype['name']), true),
      buttons(array(
          button(page_link_to('shifttypes'), _("cancel"), 'cancel'),
          button(page_link_to('shifttypes') . '&action=delete&shifttype_id=' . $shifttype['id'] . '&confirmed', _("delete"), 'ok') 
      )) 
  ));
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

function ShiftType_view($shifttype, $angeltype) {
  $parsedown = new Parsedown();
  $title = $shifttype['name'];
  if ($angeltype)
    $title .= ' <small>' . sprintf(_('for team %s'), $angeltype['name']) . '</small>';
  return page_with_title($title, [
      msg(),
      buttons([
          button(page_link_to('shifttypes'), shifttypes_title(), 'back'),
          $angeltype ? button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], $angeltype['name']) : '',
          button(page_link_to('shifttypes') . '&action=edit&shifttype_id=' . $shifttype['id'], _('edit'), 'edit'),
          button(page_link_to('shifttypes') . '&action=delete&shifttype_id=' . $shifttype['id'], _('delete'), 'delete') 
      ]),
      $parsedown->parse($shifttype['description']) 
  ]);
}

function ShiftTypes_list_view($shifttypes) {
  foreach ($shifttypes as &$shifttype) {
    $shifttype['name'] = '<a href="' . page_link_to('shifttypes') . '&action=view&shifttype_id=' . $shifttype['id'] . '">' . $shifttype['name'] . '</a>';
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