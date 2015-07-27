<?php

/**
 * Display a hint for team/angeltype coordinators if there are unconfirmed users for his angeltype.
 */
function user_angeltypes_unconfirmed_hint() {
  global $user;
  
  $unconfirmed_user_angeltypes = User_unconfirmed_AngelTypes($user);
  if ($unconfirmed_user_angeltypes === false)
    engelsystem_error("Unable to load user angeltypes.");
  if (count($unconfirmed_user_angeltypes) == 0)
    return '';
  
  $unconfirmed_links = [];
  foreach ($unconfirmed_user_angeltypes as $user_angeltype)
    $unconfirmed_links[] = '<a href="' . page_link_to('angeltypes') . '&action=view&angeltype_id=' . $user_angeltype['angeltype_id'] . '">' . $user_angeltype['name'] . ' (+' . $user_angeltype['count'] . ')' . '</a>';
  
  return info(sprintf(ngettext("There is %d unconfirmed angeltype.", "There are %d unconfirmed angeltypes.", count($unconfirmed_user_angeltypes)), count($unconfirmed_user_angeltypes)) . " " . _('Angel types which need approvals:') . ' ' . join(', ', $unconfirmed_links), true);
}

/**
 * Remove all unconfirmed users from a specific angeltype.
 */
function user_angeltypes_delete_all_controller() {
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
  
  if (! User_is_AngelType_coordinator($user, $angeltype)) {
    error(_("You are not allowed to delete all users for this angeltype."));
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
  if ($user_angeltype == null) {
    error(_("User angeltype doesn't exist."));
    redirect(page_link_to('angeltypes'));
  }
  
  if (! in_array('admin_user_angeltypes', $privileges) && ! $user_angeltype['coordinator']) {
    error(_("You are not allowed to confirm all users for this angeltype."));
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
  
  if (! User_is_AngelType_coordinator($user, $angeltype)) {
    error(_("You are not allowed to confirm this users angeltype."));
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
      UserAngelType_confirm_view($user_angeltype, $user_source, $angeltype) 
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
  
  if ($user['UID'] != $user_angeltype['user_id'] && ! User_is_AngelType_coordinator($user, $angeltype)) {
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
      UserAngelType_delete_view($user_angeltype, $user_source, $angeltype) 
  );
}

/**
 * Update an UserAngelType.
 */
function user_angeltype_update_controller() {
  global $user, $privileges;
  
  if (! in_array('admin_angel_types', $privileges)) {
    error(_("You are not allowed to set coordinator rights."));
    redirect(page_link_to('angeltypes'));
  }
  
  if (! isset($_REQUEST['user_angeltype_id'])) {
    error(_("User angeltype doesn't exist."));
    redirect(page_link_to('angeltypes'));
  }
  
  if (isset($_REQUEST['coordinator']) && preg_match("/^[01]$/", $_REQUEST['coordinator']))
    $coordinator = $_REQUEST['coordinator'] == "1";
  else {
    error(_("No coordinator update given."));
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
    $result = UserAngelType_update($user_angeltype['id'], $coordinator);
    if ($result === false)
      engelsystem_error("Unable to update coordinator rights.");
    
    $success_message = sprintf($coordinator ? _("Added coordinator rights for %s to %s.") : _("Removed coordinator rights for %s from %s."), $angeltype['name'], User_Nick_render($user_source));
    engelsystem_log($success_message);
    success($success_message);
    
    redirect(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id']);
  }
  
  return array(
      $coordinator ? _("Add coordinator rights") : _("Remove coordinator rights"),
      UserAngelType_update_view($user_angeltype, $user_source, $angeltype, $coordinator) 
  );
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
  
  if (User_is_AngelType_coordinator($user, $angeltype)) {
    // Allow to add any user
    $user_id = $user['UID'];
    
    $users_source = Users_by_angeltype_inverted($angeltype);
    if ($users_source === false)
      engelsystem_error("Unable to load users.");
    
    if (isset($_REQUEST['submit'])) {
      $ok = true;
      
      if (isset($_REQUEST['user_id']) && in_array($_REQUEST['user_id'], array_map(function ($user) {
        return $user['UID'];
      }, $users_source)))
        $user_id = $_REQUEST['user_id'];
      else {
        $ok = false;
        error(_("Please select a user."));
      }
      
      if ($ok) {
        foreach ($users_source as $user_source)
          if ($user_source['UID'] == $user_id) {
            $user_angeltype_id = UserAngelType_create($user_source, $angeltype);
            if ($user_angeltype_id === false)
              engelsystem_error("Unable to create user angeltype.");
            
            engelsystem_log(sprintf("User %s added to %s.", User_Nick_render($user_source), $angeltype['name']));
            success(sprintf(_("User %s added to %s."), User_Nick_render($user_source), $angeltype['name']));
            
            $result = UserAngelType_confirm($user_angeltype_id, $user_source);
            if ($result === false)
              engelsystem_error("Unable to confirm user angeltype.");
            engelsystem_log(sprintf("User %s confirmed as %s.", User_Nick_render($user), $angeltype['name']));
            
            redirect(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id']);
          }
      }
    }
    
    return array(
        _("Add user to angeltype"),
        UserAngelType_add_view($angeltype, $users_source, $user_id) 
    );
  } else {
    // Allow only me
    $user_angeltype = UserAngelType_by_User_and_AngelType($user, $angeltype);
    if ($user_angeltype === false)
      engelsystem_error("Unable to load user angeltype.");
    if ($user_angeltype != null) {
      error(sprintf(_("You are already a %s."), $angeltype['name']));
      redirect(page_link_to('angeltypes'));
    }
    
    if (isset($_REQUEST['confirmed'])) {
      $user_angeltype_id = UserAngelType_create($user, $angeltype);
      if ($user_angeltype_id === false)
        engelsystem_error("Unable to create user angeltype.");
      
      $success_message = sprintf(_("You joined %s."), $angeltype['name']);
      engelsystem_log(sprintf("User %s joined %s.", User_Nick_render($user), $angeltype['name']));
      success($success_message);
      
      if (in_array('admin_user_angeltypes', $privileges)) {
        $result = UserAngelType_confirm($user_angeltype_id, $user);
        if ($result === false)
          engelsystem_error("Unable to confirm user angeltype.");
        engelsystem_log(sprintf("User %s confirmed as %s.", User_Nick_render($user), $angeltype['name']));
      }
      
      redirect(page_link_to('angeltypes') . '&action=view&angeltype_id=' . $angeltype['id']);
    }
    
    return array(
        sprintf(_("Become a %s"), $angeltype['name']),
        UserAngelType_join_view($user, $angeltype) 
    );
  }
}

/**
 * Route UserAngelType actions.
 */
function user_angeltypes_controller() {
  if (! isset($_REQUEST['action']))
    redirect(page_link_to('angeltypes'));
  
  switch ($_REQUEST['action']) {
    case 'delete_all':
      return user_angeltypes_delete_all_controller();
    case 'confirm_all':
      return user_angeltypes_confirm_all_controller();
    case 'confirm':
      return user_angeltype_confirm_controller();
    case 'delete':
      return user_angeltype_delete_controller();
    case 'update':
      return user_angeltype_update_controller();
    case 'add':
      return user_angeltype_add_controller();
    default:
      redirect(page_link_to('angeltypes'));
  }
}

?>