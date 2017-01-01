<?php

function UserAngelType_update_view($user_angeltype, $user, $angeltype, $supporter) {
  return page_with_title($supporter ? _("Add supporter rights") : _("Remove supporter rights"), [
      msg(),
      info(sprintf($supporter ? _("Do you really want to add supporter rights for %s to %s?") : _("Do you really want to remove supporter rights for %s from %s?"), $angeltype['name'], User_Nick_render($user)), true),
      buttons([
          button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], _("cancel"), 'cancel'),
          button(page_link_to('user_angeltypes') . '&action=update&user_angeltype_id=' . $user_angeltype['id'] . '&supporter=' . ($supporter ? '1' : '0') . '&confirmed', _("yes"), 'ok') 
      ]) 
  ]);
}

function UserAngelTypes_delete_all_view($angeltype) {
  return page_with_title(_("Deny all users"), [
      msg(),
      info(sprintf(_("Do you really want to deny all users for %s?"), $angeltype['name']), true),
      buttons([
          button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], _("cancel"), 'cancel'),
          button(page_link_to('user_angeltypes') . '&action=delete_all&angeltype_id=' . $angeltype['id'] . '&confirmed', _("yes"), 'ok') 
      ]) 
  ]);
}

function UserAngelTypes_confirm_all_view($angeltype) {
  return page_with_title(_("Confirm all users"), [
      msg(),
      info(sprintf(_("Do you really want to confirm all users for %s?"), $angeltype['name']), true),
      buttons([
          button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], _("cancel"), 'cancel'),
          button(page_link_to('user_angeltypes') . '&action=confirm_all&angeltype_id=' . $angeltype['id'] . '&confirmed', _("yes"), 'ok') 
      ]) 
  ]);
}

function UserAngelType_confirm_view($user_angeltype, $user, $angeltype) {
  return page_with_title(_("Confirm angeltype for user"), [
      msg(),
      info(sprintf(_("Do you really want to confirm %s for %s?"), User_Nick_render($user), $angeltype['name']), true),
      buttons([
          button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], _("cancel"), 'cancel'),
          button(page_link_to('user_angeltypes') . '&action=confirm&user_angeltype_id=' . $user_angeltype['id'] . '&confirmed', _("yes"), 'ok') 
      ]) 
  ]);
}

function UserAngelType_delete_view($user_angeltype, $user, $angeltype) {
  return page_with_title(_("Remove angeltype"), [
      msg(),
      info(sprintf(_("Do you really want to delete %s from %s?"), User_Nick_render($user), $angeltype['name']), true),
      buttons([
          button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], _("cancel"), 'cancel'),
          button(page_link_to('user_angeltypes') . '&action=delete&user_angeltype_id=' . $user_angeltype['id'] . '&confirmed', _("yes"), 'ok') 
      ]) 
  ]);
}

function UserAngelType_add_view($angeltype, $users_source, $user_id) {
  $users = [];
  foreach ($users_source as $user_source) {
    $users[$user_source['UID']] = User_Nick_render($user_source);
  }
  
  return page_with_title(_("Add user to angeltype"), [
      msg(),
      buttons([
          button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], _("back"), 'back') 
      ]),
      form([
          form_info(_("Angeltype"), $angeltype['name']),
          form_select('user_id', _("User"), $users, $user_id, true),
          form_submit('submit', _("Add")) 
      ]) 
  ]);
}

function UserAngelType_join_view($user, $angeltype) {
  return page_with_title(sprintf(_("Become a %s"), $angeltype['name']), [
      msg(),
      info(sprintf(_("Do you really want to add %s to %s?"), User_Nick_render($user), $angeltype['name']), true),
      buttons([
          button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], _("cancel"), 'cancel'),
          button(page_link_to('user_angeltypes') . '&action=add&angeltype_id=' . $angeltype['id'] . '&user_id=' . $user['UID'] . '&confirmed', _("save"), 'ok') 
      ]) 
  ]);
}

?>