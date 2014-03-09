<?php

function AngelType_delete_view($angeltype) {
  return page(array(
      info(sprintf(_("Do you want to delete angeltype %s?"), $angeltype['name']), true),
      buttons(array(
          button(page_link_to('angeltypes'), _("cancel"), 'cancel'),
          button(page_link_to('angeltypes') . '&action=delete&angeltype_id=' . $angeltype['id'] . '&confirmed', _("delete"), 'ok') 
      )) 
  ));
}

function AngelType_edit_view($name, $restricted, $description) {
  return page(array(
      buttons(array(
          button(page_link_to('angeltypes'), _("Angeltypes"), 'back') 
      )),
      msg(),
      form(array(
          form_text('name', _("Name"), $name),
          form_checkbox('restricted', _("Restricted"), $restricted),
          form_info("", _("Restricted angel types can only be used by an angel if enabled by an archangel (double opt-in).")),
          form_textarea('description', _("Description"), $description),
          form_info("", _("Please use markdown for the description.")),
          form_submit('submit', _("Save")) 
      )) 
  ));
}

function AngelType_view($angeltype, $members, $user_angeltype, $admin_user_angeltypes, $admin_angeltypes) {
  $buttons = array(
      button(page_link_to('angeltypes'), _("Angeltypes"), 'back') 
  );
  
  if ($user_angeltype == null)
    $buttons[] = button(page_link_to('user_angeltypes') . '&action=add&angeltype_id=' . $angeltype['id'], _("join"), 'add');
  else {
    if ($angeltype['restricted'] && $user_angeltype['confirm_user_id'] == null)
      error(sprintf(_("You are unconfirmed for this angeltype. Please go to the introduction for %s to get confirmed."), $angeltype['name']));
    $buttons[] = button(page_link_to('user_angeltypes') . '&action=delete&user_angeltype_id=' . $user_angeltype['id'], _("leave"), 'cancel');
  }
  
  if ($admin_angeltypes) {
    $buttons[] = button(page_link_to('angeltypes') . '&action=edit&angeltype_id=' . $angeltype['id'], _("edit"), 'edit');
    $buttons[] = button(page_link_to('angeltypes') . '&action=delete&angeltype_id=' . $angeltype['id'], _("delete"), 'delete');
  }
  
  $page = array(
      msg(),
      buttons($buttons) 
  );
  
  $page[] = '<h3>' . _("Description") . '</h3>';
  $parsedown = new Parsedown();
  $page[] = $parsedown->parse($angeltype['description']);
  
  // Team-Coordinators list missing
  
  $page[] = '<h3>' . _("Members") . '</h3>';
  $members_confirmed = array();
  $members_unconfirmed = array();
  foreach ($members as $member) {
    $member['Nick'] = User_Nick_render($member);
    if ($angeltype['restricted'] && $member['confirm_user_id'] == null) {
      $member['actions'] = join(" ", array(
          '<a href="' . page_link_to('user_angeltypes') . '&action=confirm&user_angeltype_id=' . $member['user_angeltype_id'] . '" class="ok">' . ("confirm") . '</a>',
          '<a href="' . page_link_to('user_angeltypes') . '&action=delete&user_angeltype_id=' . $member['user_angeltype_id'] . '" class="cancel">' . ("deny") . '</a>' 
      ));
      $members_unconfirmed[] = $member;
    } else {
      if ($admin_user_angeltypes)
        $member['actions'] = join(" ", array(
            '<a href="' . page_link_to('user_angeltypes') . '&action=delete&user_angeltype_id=' . $member['user_angeltype_id'] . '" class="cancel">' . ("remove") . '</a>' 
        ));
      $members_confirmed[] = $member;
    }
  }
  $page[] = table(array(
      'Nick' => _("Nick"),
      'DECT' => _("DECT"),
      'actions' => "" 
  ), $members_confirmed);
  
  if ($admin_user_angeltypes && $angeltype['restricted'] && count($members_unconfirmed) > 0) {
    $page[] = '<h3>' . _("Unconfirmed") . '</h3>';
    $page[] = buttons(array(
        button(page_link_to('user_angeltypes') . '&action=confirm_all&angeltype_id=' . $angeltype['id'], _("confirm all"), 'ok'),
        button(page_link_to('user_angeltypes') . '&action=delete_all&angeltype_id=' . $angeltype['id'], _("deny all"), 'cancel') 
    ));
    $page[] = table(array(
        'Nick' => _("Nick"),
        'DECT' => _("DECT"),
        'actions' => "" 
    ), $members_unconfirmed);
  }
  
  return page($page);
}

/**
 * Display the list of angeltypes.
 *
 * @param array $angeltypes          
 */
function AngelTypes_list_view($angeltypes, $admin_angeltypes) {
  return page(array(
      msg(),
      $admin_angeltypes ? buttons(array(
          button(page_link_to('angeltypes') . '&action=edit', _("New angeltype"), 'add') 
      )) : '',
      table(array(
          'name' => _("Name"),
          'restricted' => '<img src="pic/icons/lock.png" alt="' . _("Restricted") . '" title="' . _("Restricted") . '" />',
          'membership' => _("Membership"),
          'actions' => "" 
      ), $angeltypes) 
  ));
}

function AngelTypes_about_view($angeltypes) {
  $content = array(
      '<p>' . _("Here is the list of teams and their tasks:") . '</p>' 
  );
  $parsedown = new Parsedown();
  foreach ($angeltypes as $angeltype) {
    $content[] = '<h2>' . $angeltype['name'] . '</h2>';
    if ($angeltype['restricted'])
      $content[] = info(_("This angeltype is restricted by double-opt-in by a team coordinator. Please show up at the according introduction meetings."), true);
    $content[] = $parsedown->parse($angeltype['description']);
  }
  
  return page($content);
}

?>