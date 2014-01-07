<?php

/**
 * Remove all unconfirmed users from a specific angeltype.
 */
function user_angeltypes_delete_all_controller() {
  global $user, $privileges;
  
  if (! in_array('admin_user_angeltypes', $privileges)) {
    error(_("You are not allowed to delete all users for this angeltype."));
    redirect(page_link_to('angeltypes'));
  }
  
  if (! isset($_REQUEST['angeltype_id'])) {
    error(_("Angeltype doesn't exist."));
    redirect(page_link_to('angeltypes'));
  }
  
  $angeltype = AngelType($_REQUEST['angeltype_id']);
  if ($angeltype === false)
    engelsystem_error("Unable to load angeltype.");
  if ($angeltype == null) {
    error(_("Angeltype doesn't exist."));
    redirect(page_link_to('angeltypes'));
  }
  
  if (isset($_REQUEST['confirmed'])) {
    $result = UserAngelTypes_delete_all($angeltype['id']);
    if ($result === false)
      engelsystem_error("Unable to confirm all users.");
    
    engelsystem_log(sprintf("Denied all users for angeltype %s", $angeltype['name']));
    success(sprintf(_("Denied all users for angeltype %s."), $angeltype['name']));
    redirect(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id']);
  }
  
  return array(
      _("Deny all users"),
      UserAngelTypes_delete_all_view($angeltype) 
  );
}

/**
 * Confirm all unconfirmed users for an angeltype.
 */
function user_angeltypes_confirm_all_controller() {
  global $user, $privileges;
  
  if (! in_array('admin_user_angeltypes', $privileges)) {
    error(_("You are not allowed to confirm all users for this angeltype."));
    redirect(page_link_to('angeltypes'));
  }
  
  if (! isset($_REQUEST['angeltype_id'])) {
    error(_("Angeltype doesn't exist."));
    redirect(page_link_to('angeltypes'));
  }
  
  $angeltype = AngelType($_REQUEST['angeltype_id']);
  if ($angeltype === false)
    engelsystem_error("Unable to load angeltype.");
  if ($angeltype == null) {
    error(_("Angeltype doesn't exist."));
    redirect(page_link_to('angeltypes'));
  }
  
  if (isset($_REQUEST['confirmed'])) {
    $result = UserAngelTypes_confirm_all($angeltype['id'], $user);
    if ($result === false)
      engelsystem_error("Unable to confirm all users.");
    
    engelsystem_log(sprintf("Confirmed all users for angeltype %s", $angeltype['name']));
    success(sprintf(_("Confirmed all users for angeltype %s."), $angeltype['name']));
    redirect(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id']);
  }
  
  return array(
      _("Confirm all users"),
      UserAngelTypes_confirm_all_view($angeltype) 
  );
}

/**
 * Confirm an user for an angeltype.
 */
function user_angeltype_confirm_controller() {
  global $user, $privileges;
  
  if (! in_array('admin_user_angeltypes', $privileges)) {
    error(_("You are not allowed to confirm this users angeltype."));
    redirect(page_link_to('angeltypes'));
  }
  
  if (! isset($_REQUEST['user_angeltype_id'])) {
    error(_("User angeltype doesn't exist."));
    redirect(page_link_to('angeltypes'));
  }
  
  $user_angeltype = UserAngelType($_REQUEST['user_angeltype_id']);
  if ($user_angeltype === false)
    engelsystem_error("Unable to load user angeltype.");
  if ($user_angeltype == null) {
    error(_("User angeltype doesn't exist."));
    redirect(page_link_to('angeltypes'));
  }
  
  $angeltype = AngelType($user_angeltype['angeltype_id']);
  if ($angeltype === false)
    engelsystem_error("Unable to load angeltype.");
  if ($angeltype == null) {
    error(_("Angeltype doesn't exist."));
    redirect(page_link_to('angeltypes'));
  }
  
  $user_source = User($user_angeltype['user_id']);
  if ($user_source === false)
    engelsystem_error("Unable to load user.");
  if ($user_source == null) {
    error(_("User doesn't exist."));
    redirect(page_link_to('angeltypes'));
  }
  
  if (isset($_REQUEST['confirmed'])) {
    $result = UserAngelType_confirm($user_angeltype['id'], $user);
    if ($result === false)
      engelsystem_error("Unable to confirm user angeltype.");
    
    engelsystem_log(sprintf("%s confirmed for angeltype %s", User_Nick_render($user_source), $angeltype['name']));
    success(sprintf(_("%s confirmed for angeltype %s."), User_Nick_render($user_source), $angeltype['name']));
    redirect(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id']);
  }
  
  return array(
      _("Confirm angeltype for user"),
      UserAngelType_confirm_view($user_angeltype, $user, $angeltype) 
  );
}

/**
 * Remove a user from an Angeltype.
 */
function user_angeltype_delete_controller() {
  global $user, $privileges;
  
  if (! isset($_REQUEST['user_angeltype_id'])) {
    error(_("User angeltype doesn't exist."));
    redirect(page_link_to('angeltypes'));
  }
  
  $user_angeltype = UserAngelType($_REQUEST['user_angeltype_id']);
  if ($user_angeltype === false)
    engelsystem_error("Unable to load user angeltype.");
  if ($user_angeltype == null) {
    error(_("User angeltype doesn't exist."));
    redirect(page_link_to('angeltypes'));
  }
  
  $angeltype = AngelType($user_angeltype['angeltype_id']);
  if ($angeltype === false)
    engelsystem_error("Unable to load angeltype.");
  if ($angeltype == null) {
    error(_("Angeltype doesn't exist."));
    redirect(page_link_to('angeltypes'));
  }
  
  $user_source = User($user_angeltype['user_id']);
  if ($user_source === false)
    engelsystem_error("Unable to load user.");
  if ($user_source == null) {
    error(_("User doesn't exist."));
    redirect(page_link_to('angeltypes'));
  }
  
  if ($user['UID'] != $user_angeltype['user_id'] && ! in_array('admin_user_angeltypes', $privileges)) {
    error(_("You are not allowed to delete this users angeltype."));
    redirect(page_link_to('angeltypes'));
  }
  
  if (isset($_REQUEST['confirmed'])) {
    $result = UserAngelType_delete($user_angeltype);
    if ($result === false)
      engelsystem_error("Unable to delete user angeltype.");
    
    $success_message = sprintf(_("User %s removed from %s."), User_Nick_render($user_source), $angeltype['name']);
    engelsystem_log($success_message);
    success($success_message);
    
    redirect(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id']);
  }
  
  return array(
      _("Remove angeltype"),
      UserAngelType_delete_view($user_angeltype, $user, $angeltype) 
  );
}

/**
 * Update an UserAngelType.
 */
function user_angeltype_update_controller() {

}

/**
 * User joining an Angeltype (Or Coordinator doing this for him).
 */
function user_angeltype_add_controller() {
  global $user, $privileges;
  
  if (! isset($_REQUEST['angeltype_id'])) {
    error(_("Angeltype doesn't exist."));
    redirect(page_link_to('angeltypes'));
  }
  
  $angeltype = AngelType($_REQUEST['angeltype_id']);
  if ($angeltype === false)
    engelsystem_error("Unable to load angeltype.");
  if ($angeltype == null) {
    error(_("Angeltype doesn't exist."));
    redirect(page_link_to('angeltypes'));
  }
  
  $user_angeltype = UserAngelType_by_User_and_AngelType($user, $angeltype);
  if ($user_angeltype === false)
    engelsystem_error("Unable to load user angeltype.");
  if ($user_angeltype != null) {
    error(sprintf(_("User is already an %s."), $angeltype['name']));
    redirect(page_link_to('angeltypes'));
  }
  
  if (isset($_REQUEST['confirmed'])) {
    $user_angeltype_id = UserAngelType_create($user, $angeltype);
    if ($user_angeltype_id === false)
      engelsystem_error("Unable to create user angeltype.");
    
    $success_message = sprintf(_("User %s joined %s."), User_Nick_render($user), $angeltype['name']);
    engelsystem_log($success_message);
    success($success_message);
    
    if (in_array('admin_user_angeltypes', $privileges)) {
      $result = UserAngelType_confirm($user_angeltype_id, $user);
      if ($result === false)
        engelsystem_error("Unable to confirm user angeltype.");
      $success_message = sprintf(_("User %s confirmed as %s."), User_Nick_render($user), $angeltype['name']);
      engelsystem_log($success_message);
    }
    
    redirect(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id']);
  }
  
  return array(
      _("Add user to angeltype"),
      UserAngelType_add_view($user, $angeltype) 
  );
}

/**
 * Route UserAngelType actions.
 */
function user_angeltypes_controller() {
  if (! isset($_REQUEST['action']))
    redirect(page_link_to('angeltypes'));
  
  switch ($_REQUEST['action']) {
    case 'delete_all':
      list($title, $content) = user_angeltypes_delete_all_controller();
      break;
    case 'confirm_all':
      list($title, $content) = user_angeltypes_confirm_all_controller();
      break;
    case 'confirm':
      list($title, $content) = user_angeltype_confirm_controller();
      break;
    case 'delete':
      list($title, $content) = user_angeltype_delete_controller();
      break;
    case 'update':
      list($title, $content) = user_angeltype_update_controller();
      break;
    case 'add':
      list($title, $content) = user_angeltype_add_controller();
      break;
    default:
      redirect(page_link_to('angeltypes'));
  }
  
  return array(
      $title,
      $content 
  );
}

?>