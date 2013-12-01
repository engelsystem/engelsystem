<?php
function login_title() {
  return _("Login");
}

function register_title() {
  return _("Register");
}

function logout_title() {
  return _("Logout");
}

// Engel registrieren
function guest_register() {
  global $tshirt_sizes, $enable_tshirt_size, $default_theme;
  
  $msg = "";
  $nick = "";
  $lastname = "";
  $prename = "";
  $age = "";
  $tel = "";
  $dect = "";
  $mobile = "";
  $mail = "";
  $icq = "";
  $jabber = "";
  $hometown = "";
  $comment = "";
  $tshirt_size = '';
  $password_hash = "";
  $selected_angel_types = array();
  
  $angel_types_source = sql_select("SELECT * FROM `AngelTypes` ORDER BY `name`");
  $angel_types = array();
  foreach ($angel_types_source as $angel_type)
    $angel_types[$angel_type['id']] = $angel_type['name'] . ($angel_type['restricted'] ? " (restricted)" : "");
  
  if (isset($_REQUEST['submit'])) {
    $ok = true;
    
    if (isset($_REQUEST['nick']) && strlen(strip_request_item('nick')) > 1) {
      $nick = strip_request_item('nick');
      if (sql_num_query("SELECT * FROM `User` WHERE `Nick`='" . sql_escape($nick) . "' LIMIT 1") > 0) {
        $ok = false;
        $msg .= error(sprintf(_("Your nick &quot;%s&quot; already exists."), $nick), true);
      }
    } else {
      $ok = false;
      $msg .= error(sprintf(_("Your nick &quot;%s&quot; is too short (min. 2 characters)."), strip_request_item('nick')), true);
    }
    
    if (isset($_REQUEST['mail']) && strlen(strip_request_item('mail')) > 0) {
      $mail = strip_request_item('mail');
      if (! check_email($mail)) {
        $ok = false;
        $msg .= error(_("E-mail address is not correct."), true);
      }
    } else {
      $ok = false;
      $msg .= error(_("Please enter your e-mail."), true);
    }
    
    if (isset($_REQUEST['icq']))
      $icq = strip_request_item('icq');
    if (isset($_REQUEST['jabber']) && strlen(strip_request_item('jabber')) > 0) {
      $jabber = strip_request_item('jabber');
      if (! check_email($jabber)) {
        $ok = false;
        $msg .= error(_("Please check your jabber account information."), true);
      }
    }
    
    if ($enable_tshirt_size) {
      if (isset($_REQUEST['tshirt_size']) && isset($tshirt_sizes[$_REQUEST['tshirt_size']]) && $_REQUEST['tshirt_size'] != '')
        $tshirt_size = $_REQUEST['tshirt_size'];
      else {
        $ok = false;
        $msg .= error(_("Please select your shirt size."), true);
      }
    }
    
    if (isset($_REQUEST['password']) && strlen($_REQUEST['password']) >= MIN_PASSWORD_LENGTH) {
      if ($_REQUEST['password'] != $_REQUEST['password2']) {
        $ok = false;
        $msg .= error(_("Your passwords don't match."), true);
      }
    } else {
      $ok = false;
      $msg .= error(_("Your password is to short (please use at least 6 characters)."), true);
    }
    
    $selected_angel_types = array();
    foreach ($angel_types as $angel_type_id => $angel_type_name)
      if (isset($_REQUEST['angel_types_' . $angel_type_id]))
        $selected_angel_types[] = $angel_type_id;
      
      // Trivia
    if (isset($_REQUEST['lastname']))
      $lastname = strip_request_item('lastname');
    if (isset($_REQUEST['prename']))
      $prename = strip_request_item('prename');
    if (isset($_REQUEST['age']) && preg_match("/^[0-9]{0,4}$/", $_REQUEST['age']))
      $age = strip_request_item('age');
    if (isset($_REQUEST['tel']))
      $tel = strip_request_item('tel');
    if (isset($_REQUEST['dect']))
      $dect = strip_request_item('dect');
    if (isset($_REQUEST['mobile']))
      $mobile = strip_request_item('mobile');
    if (isset($_REQUEST['hometown']))
      $hometown = strip_request_item('hometown');
    if (isset($_REQUEST['comment']))
      $comment = strip_request_item_nl('comment');
    
    if ($ok) {
      sql_query("INSERT INTO `User` SET `color`=" . sql_escape($default_theme) . ", `Nick`='" . sql_escape($nick) . "', `Vorname`='" . sql_escape($prename) . "', `Name`='" . sql_escape($lastname) . "', `Alter`='" . sql_escape($age) . "', `Telefon`='" . sql_escape($tel) . "', `DECT`='" . sql_escape($dect) . "', `Handy`='" . sql_escape($mobile) . "', `email`='" . sql_escape($mail) . "', `ICQ`='" . sql_escape($icq) . "', `jabber`='" . sql_escape($jabber) . "', `Size`='" . sql_escape($tshirt_size) . "', `Passwort`='" . sql_escape($password_hash) . "', `kommentar`='" . sql_escape($comment) . "', `Hometown`='" . sql_escape($hometown) . "', `CreateDate`=NOW(), `Sprache`='" . sql_escape($_SESSION["locale"]) . "'");
      
      // Assign user-group and set password
      $user_id = sql_id();
      sql_query("INSERT INTO `UserGroups` SET `uid`=" . sql_escape($user_id) . ", `group_id`=-2");
      set_password($user_id, $_REQUEST['password']);
      
      // Assign angel-types
      $user_angel_types_info = array();
      foreach ($selected_angel_types as $selected_angel_type_id) {
        sql_query("INSERT INTO `UserAngelTypes` SET `user_id`=" . sql_escape($user_id) . ", `angeltype_id`=" . sql_escape($selected_angel_type_id));
        $user_angel_types_info[] = $angel_types[$selected_angel_type_id];
      }
      engelsystem_log("User " . $nick . " signed up as: " . join(", ", $user_angel_types_info));
      success(_("Angel registration successful!"));
      
      redirect(page_link_to('login'));
    }
  }
  
  return page(array(
      _("By completing this form you're registering as a Chaos-Angel. This script will create you an account in the angel task sheduler."),
      $msg,
      msg(),
      form(array(
          form_text('nick', _("Nick") . "*", $nick),
          form_text('lastname', _("Last name"), $lastname),
          form_text('prename', _("First name"), $prename),
          form_text('age', _("Age"), $age),
          form_text('tel', _("Phone"), $tel),
          form_text('dect', _("DECT"), $tel),
          form_text('mobile', _("Mobile"), $mobile),
          form_text('mail', _("E-Mail") . "*", $mail),
          form_text('icq', _("ICQ"), $icq),
          form_text('jabber', _("Jabber"), $jabber),
          form_text('hometown', _("Hometown"), $hometown),
          $enable_tshirt_size ? form_select('tshirt_size', _("Shirt size"), $tshirt_sizes, $tshirt_size) : '',
          // form_textarea('comment', _("Did you help at former CCC events and which tasks have you performed then?"), $comment),
          form_checkboxes('angel_types', _("What do you want to do?") . sprintf("<br>(<a href=\"https://events.ccc.de/congress/2012/wiki/Volunteers#What_kind_of_volunteers_are_needed.3F\">%s</a>)", _("Description of job types")), $angel_types, $selected_angel_types),
          form_info("", _("Restricted angel types need will be confirmed later by an archangel. You can change your selection in the options section.")),
          form_password('password', _("Password") . "*"),
          form_password('password2', _("Confirm password") . "*"),
          info("*: " . _("Entry required!"), true),
          form_submit('submit', _("Register")) 
      )) 
  ));
}

function guest_logout() {
  session_destroy();
  redirect(page_link_to("start"));
}

function guest_login() {
  global $user;
  
  $nick = "";
  
  unset($_SESSION['uid']);
  
  if (isset($_REQUEST['submit'])) {
    $ok = true;
    
    if (isset($_REQUEST['nick']) && strlen(strip_request_item('nick')) > 0) {
      $nick = strip_request_item('nick');
      $login_user = sql_select("SELECT * FROM `User` WHERE `Nick`='" . sql_escape($nick) . "'");
      if (count($login_user) > 0) {
        $login_user = $login_user[0];
        if (isset($_REQUEST['password'])) {
          if (! verify_password($_REQUEST['password'], $login_user['Passwort'], $login_user['UID'])) {
            $ok = false;
            error(_("Your password is incorrect.  Please try it again."));
          }
        } else {
          $ok = false;
          error(_("Please enter a password."));
        }
      } else {
        $ok = false;
        error(_("No user was found with that Nickname. Please try again. If you are still having problems, ask an Dispatcher."));
      }
    } else {
      $ok = false;
      error(_("Please enter a nickname."));
    }
    
    if ($ok) {
      $_SESSION['uid'] = $login_user['UID'];
      $_SESSION['locale'] = $login_user['Sprache'];
      redirect(page_link_to('news'));
    }
  }
  
  return page(array(
      msg(),
      _("Resistance is futile! Your biological and physical parameters will be added to our collectiv! Assimilating angel:"),
      form(array(
          form_text('nick', _("Nick"), $nick),
          form_password('password', _("Password")),
          form_submit('submit', _("Login")) 
      )),
      info(_("Please note: You have to activate cookies!"), true) 
  ));
}
?>
