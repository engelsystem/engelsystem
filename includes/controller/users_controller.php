<?php

/**
 * Route user actions.
 */
function users_controller() {
  global $user;
  
  if (! isset($user)) {
    redirect(page_link_to(''));
  }
  
  if (! isset($_REQUEST['action'])) {
    $_REQUEST['action'] = 'list';
  }
  
  switch ($_REQUEST['action']) {
    default:
    case 'list':
      return users_list_controller();
    case 'view':
      return user_controller();
    case 'edit':
      return user_edit_controller();
    case 'delete':
      return user_delete_controller();
    case 'edit_vouchers':
      return user_edit_vouchers_controller();
  }
}

/**
 * Delete a user, requires to enter own password for reasons.
 */
function user_delete_controller() {
  global $privileges, $user;
  
  if (isset($_REQUEST['user_id'])) {
    $user_source = User($_REQUEST['user_id']);
  } else {
    $user_source = $user;
  }
  
  if (! in_array('admin_user', $privileges)) {
    redirect(page_link_to(''));
  }
  
  // You cannot delete yourself
  if ($user['UID'] == $user_source['UID']) {
    error(_("You cannot delete yourself."));
    redirect(user_link($user));
  }
  
  if (isset($_REQUEST['submit'])) {
    $valid = true;
    
    if (! (isset($_REQUEST['password']) && verify_password($_REQUEST['password'], $user['Passwort'], $user['UID']))) {
      $valid = false;
      error(_("Your password is incorrect.  Please try it again."));
    }
    
    if ($valid) {
      $result = User_delete($user_source['UID']);
      if ($result === false) {
        engelsystem_error('Unable to delete user.');
      }
      
      mail_user_delete($user_source);
      success(_("User deleted."));
      engelsystem_log(sprintf("Deleted %s", User_Nick_render($user_source)));
      
      redirect(users_link());
    }
  }
  
  return [
      sprintf(_("Delete %s"), $user_source['Nick']),
      User_delete_view($user_source) 
  ];
}

function users_link() {
  return page_link_to('users');
}

function user_edit_link($user) {
  return page_link_to('admin_user') . '&user_id=' . $user['UID'];
}

function user_delete_link($user) {
  return page_link_to('users') . '&action=delete&user_id=' . $user['UID'];
}

function user_link($user) {
  return page_link_to('users') . '&action=view&user_id=' . $user['UID'];
}

function user_edit_vouchers_controller() {
  global $privileges, $user;
  
  if (isset($_REQUEST['user_id'])) {
    $user_source = User($_REQUEST['user_id']);
  } else {
    $user_source = $user;
  }
  
  if (! in_array('admin_user', $privileges)) {
    redirect(page_link_to(''));
  }
  
  if (isset($_REQUEST['submit'])) {
    $valid = true;
    
    if (isset($_REQUEST['vouchers']) && test_request_int('vouchers') && trim($_REQUEST['vouchers']) >= 0) {
      $vouchers = trim($_REQUEST['vouchers']);
    } else {
      $valid = false;
      error(_("Please enter a valid number of vouchers."));
    }
    
    if ($valid) {
      $user_source['got_voucher'] = $vouchers;
      
      $result = User_update($user_source);
      if ($result === false) {
        engelsystem_error('Unable to update user.');
      }
      
      success(_("Saved the number of vouchers."));
      engelsystem_log(User_Nick_render($user_source) . ': ' . sprintf("Got %s vouchers", $user_source['got_voucher']));
      
      redirect(user_link($user_source));
    }
  }
  
  return [
      sprintf(_("%s's vouchers"), $user_source['Nick']),
      User_edit_vouchers_view($user_source) 
  ];
}

function user_controller() {
  global $privileges, $user;
  
  $user_source = $user;
  if (isset($_REQUEST['user_id'])) {
    $user_source = User($_REQUEST['user_id']);
    if ($user_source == null) {
      error(_("User not found."));
      redirect('?');
    }
  }
  
  $shifts = Shifts_by_user($user_source, in_array("user_shifts_admin", $privileges));
  foreach ($shifts as &$shift) {
    // TODO: Move queries to model
    $shift['needed_angeltypes'] = sql_select("SELECT DISTINCT `AngelTypes`.* FROM `ShiftEntry` JOIN `AngelTypes` ON `ShiftEntry`.`TID`=`AngelTypes`.`id` WHERE `ShiftEntry`.`SID`='" . sql_escape($shift['SID']) . "'  ORDER BY `AngelTypes`.`name`");
    foreach ($shift['needed_angeltypes'] as &$needed_angeltype) {
      $needed_angeltype['users'] = sql_select("
          SELECT `ShiftEntry`.`freeloaded`, `User`.*
          FROM `ShiftEntry`
          JOIN `User` ON `ShiftEntry`.`UID`=`User`.`UID`
          WHERE `ShiftEntry`.`SID`='" . sql_escape($shift['SID']) . "'
          AND `ShiftEntry`.`TID`='" . sql_escape($needed_angeltype['id']) . "'");
    }
  }
  
  if ($user_source['api_key'] == "") {
    User_reset_api_key($user_source, false);
  }
  
  return [
      $user_source['Nick'],
      User_view($user_source, in_array('admin_user', $privileges), User_is_freeloader($user_source), User_angeltypes($user_source), User_groups($user_source), $shifts, $user['UID'] == $user_source['UID']) 
  ];
}

/**
 * List all users.
 */
function users_list_controller() {
  global $privileges;
  
  if (! in_array('admin_user', $privileges)) {
    redirect(page_link_to(''));
  }
  
  $order_by = 'Nick';
  if (isset($_REQUEST['OrderBy']) && in_array($_REQUEST['OrderBy'], User_sortable_columns())) {
    $order_by = $_REQUEST['OrderBy'];
  }
  
  $users = Users($order_by);
  if ($users === false) {
    engelsystem_error('Unable to load users.');
  }
  
  foreach ($users as &$user) {
    $user['freeloads'] = count(ShiftEntries_freeloaded_by_user($user));
  }
  
  return [
      _('All users'),
      Users_view($users, $order_by, User_arrived_count(), User_active_count(), User_force_active_count(), ShiftEntries_freeleaded_count(), User_tshirts_count(), User_got_voucher_count()) 
  ];
}

/**
 * Second step of password recovery: set a new password using the token link from email
 */
function user_password_recovery_set_new_controller() {
  $user_source = User_by_password_recovery_token($_REQUEST['token']);
  if ($user_source == null) {
    error(_("Token is not correct."));
    redirect(page_link_to('login'));
  }
  
  if (isset($_REQUEST['submit'])) {
    $valid = true;
    
    if (isset($_REQUEST['password']) && strlen($_REQUEST['password']) >= MIN_PASSWORD_LENGTH) {
      if ($_REQUEST['password'] != $_REQUEST['password2']) {
        $valid = false;
        error(_("Your passwords don't match."));
      }
    } else {
      $valid = false;
      error(_("Your password is to short (please use at least 6 characters)."));
    }
    
    if ($valid) {
      set_password($user_source['UID'], $_REQUEST['password']);
      success(_("Password saved."));
      redirect(page_link_to('login'));
    }
  }
  
  return User_password_set_view();
}

/**
 * First step of password recovery: display a form that asks for your email and send email with recovery link
 */
function user_password_recovery_start_controller() {
  if (isset($_REQUEST['submit'])) {
    $valid = true;
    
    if (isset($_REQUEST['email']) && strlen(strip_request_item('email')) > 0) {
      $email = strip_request_item('email');
      if (check_email($email)) {
        $user_source = User_by_email($email);
        if ($user_source == null) {
          $valid = false;
          error(_("E-mail address is not correct."));
        }
      } else {
        $valid = false;
        error(_("E-mail address is not correct."));
      }
    } else {
      $valid = false;
      error(_("Please enter your e-mail."));
    }
    
    if ($valid) {
      $token = User_generate_password_recovery_token($user_source);
      engelsystem_email_to_user($user_source, _("Password recovery"), sprintf(_("Please visit %s to recover your password."), page_link_to_absolute('user_password_recovery') . '&token=' . $token));
      success(_("We sent an email containing your password recovery link."));
      redirect(page_link_to('login'));
    }
  }
  
  return User_password_recovery_view();
}

/**
 * User password recovery in 2 steps.
 * (By email)
 */
function user_password_recovery_controller() {
  if (isset($_REQUEST['token'])) {
    return user_password_recovery_set_new_controller();
  } else {
    return user_password_recovery_start_controller();
  }
}

/**
 * Menu title for password recovery.
 */
function user_password_recovery_title() {
  return _("Password recovery");
}

/**
 * Loads a user from param user_id.
 */
function load_user() {
  if (! isset($_REQUEST['user_id'])) {
    redirect(page_link_to());
  }
  
  $user = User($_REQUEST['user_id']);
  if ($user === false) {
    engelsystem_error("Unable to load user.");
  }
  
  if ($user == null) {
    error(_("User doesn't exist."));
    redirect(page_link_to());
  }
  
  return $user;
}

?>
