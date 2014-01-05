<?php

function UserAngelTypes_delete_all_view($angeltype) {
  return page(array(
      msg(),
      info(sprintf(_("Do you really want to deny all users for %s?"), $angeltype['name']), true),
      buttons(array(
          button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], _("cancel"), 'cancel'),
          button(page_link_to('user_angeltypes') . '&action=delete_all&angeltype_id=' . $angeltype['id'] . '&confirmed', _("yes"), 'ok') 
      )) 
  ));
}

function UserAngelTypes_confirm_all_view($angeltype) {
  return page(array(
      msg(),
      info(sprintf(_("Do you really want to confirm all users for %s?"), $angeltype['name']), true),
      buttons(array(
          button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], _("cancel"), 'cancel'),
          button(page_link_to('user_angeltypes') . '&action=confirm_all&angeltype_id=' . $angeltype['id'] . '&confirmed', _("yes"), 'ok') 
      )) 
  ));
}

function UserAngelType_confirm_view($user_angeltype, $user, $angeltype) {
  return page(array(
      msg(),
      info(sprintf(_("Do you really want to confirm %s for %s?"), User_Nick_render($user), $angeltype['name']), true),
      buttons(array(
          button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], _("cancel"), 'cancel'),
          button(page_link_to('user_angeltypes') . '&action=confirm&user_angeltype_id=' . $user_angeltype['id'] . '&confirmed', _("yes"), 'ok') 
      )) 
  ));
}

function UserAngelType_delete_view($user_angeltype, $user, $angeltype) {
  return page(array(
      msg(),
      info(sprintf(_("Do you really want to delete %s from %s?"), User_Nick_render($user), $angeltype['name']), true),
      buttons(array(
          button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], _("cancel"), 'cancel'),
          button(page_link_to('user_angeltypes') . '&action=delete&user_angeltype_id=' . $user_angeltype['id'] . '&confirmed', _("yes"), 'ok') 
      )) 
  ));
}

function UserAngelType_add_view($user, $angeltype) {
  return page(array(
      msg(),
      info(sprintf(_("Do you really want to add %s to %s?"), User_Nick_render($user), $angeltype['name']), true),
      buttons(array(
          button(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id'], _("cancel"), 'cancel'),
          button(page_link_to('user_angeltypes') . '&action=add&angeltype_id=' . $angeltype['id'] . '&user_id=' . $user['UID'] . '&confirmed', _("save"), 'ok') 
      )) 
  ));
}

?>