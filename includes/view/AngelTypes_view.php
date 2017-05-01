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
  return '<a href="' . angeltype_link($angeltype['id']) . '">' . ($angeltype['restricted'] ? glyph('lock') : '') . $angeltype['name'] . '</a>';
}

/**
 * Render angeltype membership state
 *
 * @param UserAngelType $user_angeltype
 *          UserAngelType and AngelType
 * @return string
 */
function AngelType_render_membership($user_angeltype) {
  if ($user_angeltype['user_angeltype_id'] != null) {
    if ($user_angeltype['restricted']) {
      if ($user_angeltype['confirm_user_id'] == null) {
        return glyph('lock') . _("Unconfirmed");
      } elseif ($user_angeltype['supporter']) {
        return glyph_bool(true) . _("supporter");
      }
      return glyph_bool(true) . _("Member");
    } elseif ($user_angeltype['supporter']) {
      return glyph_bool(true) . _("supporter");
    }
    return glyph_bool(true) . _("Member");
  }
  return glyph_bool(false);
}

function AngelType_delete_view($angeltype) {
  return page_with_title(sprintf(_("Delete angeltype %s"), $angeltype['name']), [
      info(sprintf(_("Do you want to delete angeltype %s?"), $angeltype['name']), true),
      buttons([
          button(page_link_to('angeltypes'), _("cancel"), 'cancel'),
          button(page_link_to('angeltypes') . '&action=delete&angeltype_id=' . $angeltype['id'] . '&confirmed', _("delete"), 'ok') 
      ]) 
  ]);
}

/**
 * Render angeltype edit form.
 *
 * @param Angeltype $angeltype
 *          The angeltype to edit
 * @param boolean $supporter_mode
 *          Is the user a supporter of this angeltype?
 */
function AngelType_edit_view($angeltype, $supporter_mode) {
  $contact_info = AngelType_contact_info($angeltype);
  return page_with_title(sprintf(_("Edit %s"), $angeltype['name']), [
      buttons([
          button(page_link_to('angeltypes'), _("Angeltypes"), 'back') 
      ]),
      msg(),
      form([
          $supporter_mode ? form_info(_("Name"), $angeltype['name']) : form_text('name', _("Name"), $angeltype['name']),
          $supporter_mode ? form_info(_("Restricted"), $angeltype['restricted'] ? _("Yes") : _("No")) : form_checkbox('restricted', _("Restricted"), $angeltype['restricted']),
          $supporter_mode ? form_info(_("No Self Sign Up"), $angeltype['no_self_signup'] ? _("Yes") : _("No")) : form_checkbox('no_self_signup', _("No Self Sign Up"), $angeltype['no_self_signup']),
          $supporter_mode ? form_info(_("Requires driver license"), $angeltype['requires_driver_license'] ? _("Yes") : _("No")) : form_checkbox('requires_driver_license', _("Requires driver license"), $angeltype['requires_driver_license']),
          //form_text('contact_name', _("Name"), $angeltype['contact_name']),
          //form_text('contact_dect', _("DECT"), $angeltype['contact_dect']),
          //form_text('contact_email', _("E-Mail"), $angeltype['contact_email']),
          form_info("", _("Restricted angel types can only be used by an angel if enabled by a supporter (double opt-in).")),
          form_textarea('description', _("Description"), $angeltype['description']),
          form_info("", _("Please use markdown for the description.")),
          form_submit('submit', _("Save")) 
      ]) 
  ]);
}

/**
 * Renders the buttons for the angeltype view.
 */
function AngelType_view_buttons($angeltype, $user_angeltype, $admin_angeltypes, $supporter, $user_driver_license, $user) {
  $buttons = [
      button(page_link_to('angeltypes'), _("Angeltypes"), 'back') 
  ];
  
  if ($angeltype['requires_driver_license']) {
    $buttons[] = button(user_driver_license_edit_link($user), glyph("road") . _("my driving license"));
  }
  
  if ($user_angeltype == null) {
    $buttons[] = button(page_link_to('user_angeltypes') . '&action=add&angeltype_id=' . $angeltype['id'], _("join"), 'add');
  } else {
    if ($angeltype['requires_driver_license'] && $user_driver_license == null) {
      error(_("This angeltype requires a driver license. Please enter your driver license information!"));
    }
    
    if ($angeltype['restricted'] && $user_angeltype['confirm_user_id'] == null) {
      error(sprintf(_("You are unconfirmed for this angeltype. Please go to the introduction for %s to get confirmed."), $angeltype['name']));
    }
    $buttons[] = button(page_link_to('user_angeltypes') . '&action=delete&user_angeltype_id=' . $user_angeltype['id'], _("leave"), 'cancel');
  }
  
  if ($admin_angeltypes || $supporter) {
    $buttons[] = button(page_link_to('angeltypes') . '&action=edit&angeltype_id=' . $angeltype['id'], _("edit"), 'edit');
  }
  if ($admin_angeltypes) {
    $buttons[] = button(page_link_to('angeltypes') . '&action=delete&angeltype_id=' . $angeltype['id'], _("delete"), 'delete');
  }
  
  return buttons($buttons);
}

/**
 * Renders and sorts the members of an angeltype into supporters, members and unconfirmed members.
 *
 * @return [supporters, members, unconfirmed members]
 */
function AngelType_view_members($angeltype, $members, $admin_user_angeltypes, $admin_angeltypes) {
  $supporters = [];
  $members_confirmed = [];
  $members_unconfirmed = [];
  foreach ($members as $member) {
    $member['Nick'] = User_Nick_render($member);

    if ($angeltype['requires_driver_license']) {
      $member['wants_to_drive'] = glyph_bool($member['wants_to_drive']);
      $member['has_car'] = glyph_bool($member['has_car']);
      $member['has_license_car'] = glyph_bool($member['has_license_car']);
      $member['has_license_3_5t_transporter'] = glyph_bool($member['has_license_3_5t_transporter']);
      $member['has_license_7_5t_truck'] = glyph_bool($member['has_license_7_5t_truck']);
      $member['has_license_12_5t_truck'] = glyph_bool($member['has_license_12_5t_truck']);
      $member['has_license_forklift'] = glyph_bool($member['has_license_forklift']);
    }
    
    if ($angeltype['restricted'] && $member['confirm_user_id'] == null) {
      $member['actions'] = table_buttons([
          button(page_link_to('user_angeltypes') . '&action=confirm&user_angeltype_id=' . $member['user_angeltype_id'], _("confirm"), 'btn-xs'),
          button(page_link_to('user_angeltypes') . '&action=delete&user_angeltype_id=' . $member['user_angeltype_id'], _("deny"), 'btn-xs') 
      ]);
      $members_unconfirmed[] = $member;
    } elseif ($member['supporter']) {
      if ($admin_angeltypes) {
        $member['actions'] = table_buttons([
            button(page_link_to('user_angeltypes') . '&action=update&user_angeltype_id=' . $member['user_angeltype_id'] . '&supporter=0', _("Remove supporter rights"), 'btn-xs') 
        ]);
      } else {
        $member['actions'] = '';
      }
      $supporters[] = $member;
    } else {
      if ($admin_user_angeltypes) {
        $member['actions'] = table_buttons([
            $admin_angeltypes ? button(page_link_to('user_angeltypes') . '&action=update&user_angeltype_id=' . $member['user_angeltype_id'] . '&supporter=1', _("Add supporter rights"), 'btn-xs') : '',
            button(page_link_to('user_angeltypes') . '&action=delete&user_angeltype_id=' . $member['user_angeltype_id'], _("remove"), 'btn-xs') 
        ]);
      }
      $members_confirmed[] = $member;
    }
  }
  
  return [
      $supporters,
      $members_confirmed,
      $members_unconfirmed 
  ];
}

/**
 * Creates the needed member table headers according to given rights and settings from the angeltype.
 */
function AngelType_view_table_headers($angeltype, $supporter, $admin_angeltypes) {
  if ($angeltype['requires_driver_license'] && ($supporter || $admin_angeltypes)) {
    return [
        'Nick' => _("Nick"),
        'DECT' => _("DECT"),
        'wants_to_drive' => _("Driver"),
        'has_car' => _("Has car"),
        'has_license_car' => _("Car"),
        'has_license_3_5t_transporter' => _("3,5t Transporter"),
        'has_license_7_5t_truck' => _("7,5t Truck"),
        'has_license_12_5t_truck' => _("12,5t Truck"),
        'has_license_forklift' => _("Forklift"),
        'actions' => '' 
    ];
  }
  return [
      'Nick' => _("Nick"),
      'DECT' => _("DECT"),
      'actions' => '' 
  ];
}

/**
 * Render an angeltype page containing the member lists.
 */
function AngelType_view($angeltype, $members, $user_angeltype, $admin_user_angeltypes, $admin_angeltypes, $supporter, $user_driver_license, $user) {
  $page = [
      AngelType_view_buttons($angeltype, $user_angeltype, $admin_angeltypes, $supporter, $user_driver_license, $user),
      msg() 
  ];
  
  $page[] = '<h3>' . _("Description") . '</h3>';
  $parsedown = new Parsedown();
  if ($angeltype['description'] != "") {
    $page[] = '<div class="well">' . $parsedown->parse($angeltype['description']) . '</div>';
  }
  
  list($supporters, $members_confirmed, $members_unconfirmed) = AngelType_view_members($angeltype, $members, $admin_user_angeltypes, $admin_angeltypes);
  $table_headers = AngelType_view_table_headers($angeltype, $supporter, $admin_angeltypes);
  
  if (count($supporters) > 0) {
    $page[] = '<h3>' . _("supporters") . '</h3>';
    $page[] = table($table_headers, $supporters);
  }
  
  if (count($members_confirmed) > 0) {
    $members_confirmed[] = [
        'Nick' => _('Sum'),
        'DECT' => count($members_confirmed),
        'actions' => '' 
    ];
  }
  
  if (count($members_unconfirmed) > 0) {
    $members_unconfirmed[] = [
        'Nick' => _('Sum'),
        'DECT' => count($members_unconfirmed),
        'actions' => '' 
    ];
  }
  
  $page[] = '<h3>' . _("Members") . '</h3>';
  if ($admin_user_angeltypes) {
    $page[] = buttons([
        button(page_link_to('user_angeltypes') . '&action=add&angeltype_id=' . $angeltype['id'], _("Add"), 'add') 
    ]);
  }
  $page[] = table($table_headers, $members_confirmed);
  
  if ($admin_user_angeltypes && $angeltype['restricted'] && count($members_unconfirmed) > 0) {
    $page[] = '<h3>' . _("Unconfirmed") . '</h3>';
    $page[] = buttons([
        button(page_link_to('user_angeltypes') . '&action=confirm_all&angeltype_id=' . $angeltype['id'], _("confirm all"), 'ok'),
        button(page_link_to('user_angeltypes') . '&action=delete_all&angeltype_id=' . $angeltype['id'], _("deny all"), 'cancel') 
    ]);
    $page[] = table($table_headers, $members_unconfirmed);
  }
  
  return page_with_title(sprintf(_("Team %s"), $angeltype['name']), $page);
}

/**
 * Display the list of angeltypes.
 *
 * @param array $angeltypes          
 */
function AngelTypes_list_view($angeltypes, $admin_angeltypes) {
  return page_with_title(angeltypes_title(), [
      msg(),
      buttons([
          $admin_angeltypes ? button(page_link_to('angeltypes') . '&action=edit', _("New angeltype"), 'add') : '',
          button(page_link_to('angeltypes') . '&action=about', _("Teams/Job description")) 
      ]),
      table([
          'name' => _("Name"),
          'restricted' => glyph('lock') . _("Restricted"),
          'no_self_signup' => glyph('share') . _("Self Sign Up Allowed"),
          'membership' => _("Membership"),
          'actions' => "" 
      ], $angeltypes) 
  ]);
}

/**
 * Renders the about info for an angeltype.
 */
function AngelTypes_about_view_angeltype($angeltype) {
  $parsedown = new Parsedown();
  
  $html = '<h2>' . $angeltype['name'] . '</h2>';
  
  if (isset($angeltype['user_angeltype_id'])) {
    $buttons = [];
    if ($angeltype['user_angeltype_id'] != null) {
      $buttons[] = button(page_link_to('user_angeltypes') . '&action=delete&user_angeltype_id=' . $angeltype['user_angeltype_id'], _("leave"), 'cancel');
    } else {
      $buttons[] = button(page_link_to('user_angeltypes') . '&action=add&angeltype_id=' . $angeltype['id'], _("join"), 'add');
    }
    $html .= buttons($buttons);
  }
  
  if ($angeltype['restricted']) {
    $html .= info(_("This angeltype is restricted by double-opt-in by a team supporter. Please show up at the according introduction meetings."), true);
  }
  if ($angeltype['description'] != "") {
    $html .= '<div class="well">' . $parsedown->parse($angeltype['description']) . '</div>';
  }
  $html .= '<hr />';
  
  return $html;
}

/**
 * Renders a site that contains every angeltype and its description, basically as an overview of the needed help types.
 */
function AngelTypes_about_view($angeltypes, $user_logged_in) {
  global $faq_url;
  
  $content = [
      buttons([
          ! $user_logged_in ? button(page_link_to('register'), register_title()) : '',
          ! $user_logged_in ? button(page_link_to('login'), login_title()) : '',
          $user_logged_in ? button(page_link_to('angeltypes'), angeltypes_title(), 'back') : '',
          button($faq_url, _("FAQ"), "btn-primary") 
      ]),
      '<p>' . _("Here is the list of teams and their tasks. If you have questions, read the FAQ.") . '</p>',
      '<hr />' 
  ];
  foreach ($angeltypes as $angeltype) {
    $content[] = AngelTypes_about_view_angeltype($angeltype);
  }
  
  return page_with_title(_("Teams/Job description"), $content);
}

?>
