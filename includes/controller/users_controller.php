<?php

/**
 * User password recovery. (By email)
 */
function user_password_recovery_controller() {
  if (isset($_REQUEST['token'])) {
    $user_source = User_by_password_recovery_token($_REQUEST['token']);
    if ($user_source === false)
      engelsystem_error("Unable to load user.");
    if ($user_source == null) {
      error(_("Token is not correct."));
      redirect(page_link_to('login'));
    }
    
    if (isset($_REQUEST['submit'])) {
      $ok = true;
      
      if (isset($_REQUEST['password']) && strlen($_REQUEST['password']) >= MIN_PASSWORD_LENGTH) {
        if ($_REQUEST['password'] != $_REQUEST['password2']) {
          $ok = false;
          error(_("Your passwords don't match."));
        }
      } else {
        $ok = false;
        error(_("Your password is to short (please use at least 6 characters)."));
      }
      
      if ($ok) {
        $result = set_password($user_source['UID'], $_REQUEST['password']);
        if ($result === false)
          engelsystem_error(_("Password could not be updated."));
        
        success(_("Password saved."));
        redirect(page_link_to('login'));
      }
    }
    
    return User_password_set_view();
  } else {
    if (isset($_REQUEST['submit'])) {
      $ok = true;
      
      if (isset($_REQUEST['email']) && strlen(strip_request_item('email')) > 0) {
        $email = strip_request_item('email');
        if (check_email($email)) {
          $user_source = User_by_email($email);
          if ($user_source === false)
            engelsystem_error("Unable to load user.");
          if ($user_source == null) {
            $ok = false;
            $msg .= error(_("E-mail address is not correct."), true);
          }
        } else {
          $ok = false;
          $msg .= error(_("E-mail address is not correct."), true);
        }
      } else {
        $ok = false;
        $msg .= error(_("Please enter your e-mail."), true);
      }
      
      if ($ok) {
        $token = User_generate_password_recovery_token($user_source);
        if ($token === false)
          engelsystem_error("Unable to generate password recovery token.");
        $result = engelsystem_email_to_user($user_source, _("Password recovery"), sprintf(_("Please visit %s to recover your password."), page_link_to_absolute('user_password_recovery') . '&token=' . $token));
        if ($result === false)
          engelsystem_error("Unable to send password recovery email.");
        
        success(_("We sent an email containing your password recovery link."));
        redirect(page_link_to('login'));
      }
    }
    
    return User_password_recovery_view();
  }
}

function user_password_recovery_title() {
  return _("Password recovery");
}

?>