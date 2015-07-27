<?php

/**
 * AngelTypes
 */

/**
 * Renders the angeltypes name as link.
 *
 * @param AngelType $angeltype          
 */
function AngelType_name_render($angeltype) {
  return '<a href="' . page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'] . '">' . ($angeltype['restricted'] ? glyph('lock') : '') . $angeltype['name'] . '</a>';
}

/**
 * Render angeltype membership state
 *
 * @param UserAngelType $user_angeltype
 *          UserAngelType and AngelType
 * @return string
 */
function AngelType_render_membership($user_angeltype) {
  $membership = "";
  if ($user_angeltype['user_angeltype_id'] != null) {
    if ($user_angeltype['restricted']) {
      if ($user_angeltype['confirm_user_id'] == null)
        $membership = glyph('lock') . _("Unconfirmed");
      elseif ($user_angeltype['coordinator'])
        $membership = glyph_bool(true) . _("Coordinator");
      else
        $membership = glyph_bool(true) . _("Member");
    } elseif ($user_angeltype['coordinator'])
      $membership = glyph_bool(true) . _("Coordinator");
    else
      $membership = glyph_bool(true) . _("Member");
  } else {
    $membership = glyph_bool(false);
  }
  return $membership;
}

function AngelType_delete_view($angeltype) {
  return page_with_title(sprintf(_("Delete angeltype %s"), $angeltype['name']), array(
      info(sprintf(_("Do you want to delete angeltype %s?"), $angeltype['name']), true),
      buttons(array(
          button(page_link_to('angeltypes'), _("cancel"), 'cancel'),
          button(page_link_to('angeltypes') . '&action=delete&angeltype_id=' . $angeltype['id'] . '&confirmed', _("delete"), 'ok') 
      )) 
  ));
}

function AngelType_edit_view($name, $restricted, $description, $coordinator_mode) {
  return page_with_title(sprintf(_("Edit %s"), $name), array(
      buttons(array(
          button(page_link_to('angeltypes'), _("Angeltypes"), 'back') 
      )),
      msg(),
      form(array(
          $coordinator_mode ? form_info(_("Name"), $name) : form_text('name', _("Name"), $name),
          $coordinator_mode ? form_info(_("Restricted"), $restricted ? _("Yes") : _("No")) : form_checkbox('restricted', _("Restricted"), $restricted),
          form_info("", _("Restricted angel types can only be used by an angel if enabled by an archangel (double opt-in).")),
          form_textarea('description', _("Description"), $description),
          form_info("", _("Please use markdown for the description.")),
          form_submit('submit', _("Save")) 
      )) 
  ));
}

function AngelType_view($angeltype, $members, $user_angeltype, $admin_user_angeltypes, $admin_angeltypes, $coordinator) {
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
  
  if ($admin_angeltypes || $coordinator)
    $buttons[] = button(page_link_to('angeltypes') . '&action=edit&angeltype_id=' . $angeltype['id'], _("edit"), 'edit');
  if ($admin_angeltypes)
    $buttons[] = button(page_link_to('angeltypes') . '&action=delete&angeltype_id=' . $angeltype['id'], _("delete"), 'delete');
  
  $page = array(
      msg(),
      buttons($buttons) 
  );
  
  $page[] = '<h3>' . _("Description") . '</h3>';
  $parsedown = new Parsedown();
  if ($angeltype['description'] != "")
    $page[] = '<div class="well">' . $parsedown->parse($angeltype['description']) . '</div>';
    
    // Team-Coordinators list missing
  
  $coordinators = array();
  $members_confirmed = array();
  $members_unconfirmed = array();
  foreach ($members as $member) {
    $member['Nick'] = User_Nick_render($member);
    if ($angeltype['restricted'] && $member['confirm_user_id'] == null) {
      $member['actions'] = table_buttons([
          button(page_link_to('user_angeltypes') . '&action=confirm&user_angeltype_id=' . $member['user_angeltype_id'], _("confirm"), 'btn-xs'),
          button(page_link_to('user_angeltypes') . '&action=delete&user_angeltype_id=' . $member['user_angeltype_id'], _("deny"), 'btn-xs') 
      ]);
      $members_unconfirmed[] = $member;
    } elseif ($member['coordinator']) {
      if ($admin_angeltypes)
        $member['actions'] = table_buttons([
            button(page_link_to('user_angeltypes') . '&action=update&user_angeltype_id=' . $member['user_angeltype_id'] . '&coordinator=0', _("Remove coordinator rights"), 'btn-xs') 
        ]);
      else
        $member['actions'] = '';
      $coordinators[] = $member;
    } else {
      if ($admin_user_angeltypes)
        $member['actions'] = table_buttons([
            $admin_angeltypes ? button(page_link_to('user_angeltypes') . '&action=update&user_angeltype_id=' . $member['user_angeltype_id'] . '&coordinator=1', _("Add coordinator rights"), 'btn-xs') : '',
            button(page_link_to('user_angeltypes') . '&action=delete&user_angeltype_id=' . $member['user_angeltype_id'], _("remove"), 'btn-xs') 
        ]);
      $members_confirmed[] = $member;
    }
  }
  if (count($coordinators) > 0) {
    $page[] = '<h3>' . _("Coordinators") . '</h3>';
    $page[] = table(array(
        'Nick' => _("Nick"),
        'DECT' => _("DECT"),
        'actions' => "" 
    ), $coordinators);
  }
  
  if (count($members_confirmed) > 0)
    $members_confirmed[] = [
        'Nick' => _('Sum'),
        'DECT' => count($members_confirmed),
        'actions' => '' 
    ];
  
  if (count($members_unconfirmed) > 0)
    $members_unconfirmed[] = [
        'Nick' => _('Sum'),
        'DECT' => count($members_unconfirmed),
        'actions' => '' 
    ];
  
  $page[] = '<h3>' . _("Members") . '</h3>';
  if ($admin_user_angeltypes)
    $page[] = buttons(array(
        button(page_link_to('user_angeltypes') . '&action=add&angeltype_id=' . $angeltype['id'], _("Add"), 'add') 
    ));
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
  
  return page_with_title(sprintf(_("Team %s"), $angeltype['name']), $page);
}

/**
 * Display the list of angeltypes.
 *
 * @param array $angeltypes          
 */
function AngelTypes_list_view($angeltypes, $admin_angeltypes) {
  return page_with_title(angeltypes_title(), array(
      msg(),
      buttons(array(
          $admin_angeltypes ? button(page_link_to('angeltypes') . '&action=edit', _("New angeltype"), 'add') : '',
          button(page_link_to('angeltypes') . '&action=about', _("Teams/Job description")) 
      )),
      table(array(
          'name' => _("Name"),
          'restricted' => glyph('lock') . _("Restricted"),
          'membership' => _("Membership"),
          'actions' => "" 
      ), $angeltypes) 
  ));
}

function AngelTypes_about_view($angeltypes, $user_logged_in) {
  global $faq_url;
  
  $content = array(
      buttons(array(
          ! $user_logged_in ? button(page_link_to('register'), register_title()) : '',
          ! $user_logged_in ? button(page_link_to('login'), login_title()) : '',
          $user_logged_in ? button(page_link_to('angeltypes'), angeltypes_title(), 'back') : '',
          button($faq_url, _("FAQ"), "btn-primary") 
      )),
      '<p>' . _("Here is the list of teams and their tasks. If you have questions, read the FAQ.") . '</p>',
      '<hr />' 
  );
  $parsedown = new Parsedown();
  foreach ($angeltypes as $angeltype) {
    $content[] = '<h2>' . $angeltype['name'] . '</h2>';
    
    if (isset($angeltype['user_angeltype_id'])) {
      $buttons = array();
      if ($angeltype['user_angeltype_id'] != null)
        $buttons[] = button(page_link_to('user_angeltypes') . '&action=delete&user_angeltype_id=' . $angeltype['user_angeltype_id'], _("leave"), 'cancel');
      else
        $buttons[] = button(page_link_to('user_angeltypes') . '&action=add&angeltype_id=' . $angeltype['id'], _("join"), 'add');
      $content[] = buttons($buttons);
    }
    
    if ($angeltype['restricted'])
      $content[] = info(_("This angeltype is restricted by double-opt-in by a team coordinator. Please show up at the according introduction meetings."), true);
    if ($angeltype['description'] != "")
      $content[] = '<div class="well">' . $parsedown->parse($angeltype['description']) . '</div>';
    $content[] = '<hr />';
  }
  
  return page_with_title(_("Teams/Job description"), $content);
}

?>
