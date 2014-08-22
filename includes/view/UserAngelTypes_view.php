<?php
function UserAngelType_update_view($user_angeltype, $user, $angeltype, $coordinator) {
  return page_with_title($coordinator ? _("Add coordinator rights") : _("Remove coordinator rights"), array(
      msg(),
      info(sprintf($coordinator ? _("Do you really want to add coordinator rights for %s to %s?") : _("Do you really want to remove coordinator rights for %s from %s?"), $angeltype['name'], User_Nick_render($user)), true),
      buttons(array(
          button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], _("cancel"), 'cancel'),
          button(page_link_to('user_angeltypes') . '&action=update&user_angeltype_id=' . $user_angeltype['id'] . '&coordinator=' . ($coordinator ? '1' : '0') . '&confirmed', _("yes"), 'ok') 
      )) 
  ));
}

function UserAngelTypes_delete_all_view($angeltype) {
  return page_with_title(_("Deny all users"), array(
      msg(),
      info(sprintf(_("Do you really want to deny all users for %s?"), $angeltype['name']), true),
      buttons(array(
          button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], _("cancel"), 'cancel'),
          button(page_link_to('user_angeltypes') . '&action=delete_all&angeltype_id=' . $angeltype['id'] . '&confirmed', _("yes"), 'ok') 
      )) 
  ));
}

function UserAngelTypes_confirm_all_view($angeltype) {
  return page_with_title(_("Confirm all users"), array(
      msg(),
      info(sprintf(_("Do you really want to confirm all users for %s?"), $angeltype['name']), true),
      buttons(array(
          button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], _("cancel"), 'cancel'),
          button(page_link_to('user_angeltypes') . '&action=confirm_all&angeltype_id=' . $angeltype['id'] . '&confirmed', _("yes"), 'ok') 
      )) 
  ));
}

function UserAngelType_confirm_view($user_angeltype, $user, $angeltype) {
  return page_with_title(_("Confirm angeltype for user"), array(
      msg(),
      info(sprintf(_("Do you really want to confirm %s for %s?"), User_Nick_render($user), $angeltype['name']), true),
      buttons(array(
          button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], _("cancel"), 'cancel'),
          button(page_link_to('user_angeltypes') . '&action=confirm&user_angeltype_id=' . $user_angeltype['id'] . '&confirmed', _("yes"), 'ok') 
      )) 
  ));
}

function UserAngelType_delete_view($user_angeltype, $user, $angeltype) {
  return page_with_title(_("Remove angeltype"), array(
      msg(),
      info(sprintf(_("Do you really want to delete %s from %s?"), User_Nick_render($user), $angeltype['name']), true),
      buttons(array(
          button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], _("cancel"), 'cancel'),
          button(page_link_to('user_angeltypes') . '&action=delete&user_angeltype_id=' . $user_angeltype['id'] . '&confirmed', _("yes"), 'ok') 
      )) 
  ));
}

function UserAngelType_add_view($angeltype, $users_source, $user_id) {
  $users = array();
  foreach ($users_source as $user_source)
    $users[$user_source['UID']] = User_Nick_render($user_source);
  
  return page_with_title(_("Add user to angeltype"), array(
      msg(),
      buttons(array(
          button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], _("back"), 'back') 
      )),
      form(array(
          form_info(_("Angeltype"), $angeltype['name']),
          form_select('user_id', _("User"), $users, $user_id),
          form_submit('submit', _("Add")) 
      )) 
  ));
}

function UserAngelType_join_view($user, $angeltype) {
  return page_with_title(sprintf(_("Become a %s"), $angeltype['name']), array(
      msg(),
      info(sprintf(_("Do you really want to add %s to %s?"), User_Nick_render($user), $angeltype['name']), true),
      buttons(array(
          button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], _("cancel"), 'cancel'),
          button(page_link_to('user_angeltypes') . '&action=add&angeltype_id=' . $angeltype['id'] . '&user_id=' . $user['UID'] . '&confirmed', _("save"), 'ok') 
      )) 
  ));
}

?>