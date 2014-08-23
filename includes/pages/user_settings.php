<?php
function settings_title() {
  return _("Settings");
}

function user_settings() {
  global $enable_tshirt_size, $tshirt_sizes, $themes, $locales;
  global $user;
  
  $msg = "";
  $nick = $user['Nick'];
  $lastname = $user['Name'];
  $prename = $user['Vorname'];
  $age = $user['Alter'];
  $tel = $user['Telefon'];
  $dect = $user['DECT'];
  $mobile = $user['Handy'];
  $mail = $user['email'];
  $icq = $user['ICQ'];
  $jabber = $user['jabber'];
  $hometown = $user['Hometown'];
  $tshirt_size = $user['Size'];
  $password_hash = "";
  $selected_theme = $user['color'];
  $selected_language = $user['Sprache'];
  
  $selected_angel_types_source = sql_select("SELECT * FROM `UserAngelTypes` WHERE `user_id`=" . sql_escape($user['UID']));
  $selected_angel_types = array();
  foreach ($selected_angel_types_source as $selected_angel_type)
    $selected_angel_types[] = $selected_angel_type['angeltype_id'];
  
  $angel_types_source = sql_select("SELECT * FROM `AngelTypes` ORDER BY `name`");
  $angel_types = array();
  foreach ($angel_types_source as $angel_type)
    $angel_types[$angel_type['id']] = $angel_type['name'] . ($angel_type['restricted'] ? " (restricted)" : "");
  
  if (isset($_REQUEST['submit'])) {
    $ok = true;
    
    if (isset($_REQUEST['nick']) && strlen(strip_request_item('nick')) > 1) {
      $nick = strip_request_item('nick');
      if (sql_num_query("SELECT * FROM `User` WHERE `Nick`='" . sql_escape($nick) . "' AND NOT `UID`=" . sql_escape($user['UID']) . " LIMIT 1") > 0) {
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
    
    if (isset($_REQUEST['tshirt_size']) && isset($tshirt_sizes[$_REQUEST['tshirt_size']]))
      $tshirt_size = $_REQUEST['tshirt_size'];
    elseif ($enable_tshirt_size) {
      $ok = false;
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
    
    if ($ok) {
      sql_query("UPDATE `User` SET `Nick`='" . sql_escape($nick) . "', `Vorname`='" . sql_escape($prename) . "', `Name`='" . sql_escape($lastname) . "', `Alter`='" . sql_escape($age) . "', `Telefon`='" . sql_escape($tel) . "', `DECT`='" . sql_escape($dect) . "', `Handy`='" . sql_escape($mobile) . "', `email`='" . sql_escape($mail) . "', `ICQ`='" . sql_escape($icq) . "', `jabber`='" . sql_escape($jabber) . "', `Size`='" . sql_escape($tshirt_size) . "', `Hometown`='" . sql_escape($hometown) . "' WHERE `UID`=" . sql_escape($user['UID']));
      
      // Assign angel-types
      $user_angel_type_info = array();
      $deleted_angel_types = array_diff(array_keys($angel_types), $selected_angel_types);
      if (count($deleted_angel_types) > 0)
        sql_query("DELETE FROM `UserAngelTypes` WHERE `user_id`='" . sql_escape($user['UID']) . "' AND `angeltype_id` IN (" . implode(",", $deleted_angel_types) . ")");
      foreach ($angel_types_source as $angel_type)
        if (in_array($angel_type['id'], $selected_angel_types))
          $user_angel_type_info[] = $angel_type['name'];
      
      foreach ($selected_angel_types as $selected_angel_type_id) {
        if (sql_num_query("SELECT * FROM `UserAngelTypes` WHERE `user_id`=" . sql_escape($user['UID']) . " AND `angeltype_id`=" . sql_escape($selected_angel_type_id) . " LIMIT 1") == 0)
          sql_query("INSERT INTO `UserAngelTypes` SET `user_id`=" . sql_escape($user['UID']) . ", `angeltype_id`=" . sql_escape($selected_angel_type_id));
      }
      
      engelsystem_log("Own angel types set to: " . join(", ", $user_angel_type_info));
      success(_("Settings saved."));
      redirect(page_link_to('user_settings'));
    }
  } elseif (isset($_REQUEST['submit_password'])) {
    $ok = true;
    
    if (! isset($_REQUEST['password']) || ! verify_password($_REQUEST['password'], $user['Passwort'], $user['UID']))
      $msg .= error(_("-> not OK. Please try again."), true);
    elseif (strlen($_REQUEST['new_password']) < MIN_PASSWORD_LENGTH)
      $msg .= error(_("Your password is to short (please use at least 6 characters)."), true);
    elseif ($_REQUEST['new_password'] != $_REQUEST['new_password2'])
      $msg .= error(_("Your passwords don't match."), true);
    elseif (set_password($user['UID'], $_REQUEST['new_password']))
      success(_("Password saved."));
    else
      error(_("Failed setting password."));
    redirect(page_link_to('user_settings'));
  } elseif (isset($_REQUEST['submit_theme'])) {
    $ok = true;
    
    if (isset($_REQUEST['theme']) && isset($themes[$_REQUEST['theme']]))
      $selected_theme = $_REQUEST['theme'];
    else
      $ok = false;
    
    if ($ok) {
      sql_query("UPDATE `User` SET `color`='" . sql_escape($selected_theme) . "' WHERE `UID`=" . sql_escape($user['UID']));
      
      success(_("Theme changed."));
      redirect(page_link_to('user_settings'));
    }
  } elseif (isset($_REQUEST['submit_language'])) {
    $ok = true;
    
    if (isset($_REQUEST['language']) && isset($locales[$_REQUEST['language']]))
      $selected_language = $_REQUEST['language'];
    else
      $ok = false;
    
    if ($ok) {
      sql_query("UPDATE `User` SET `Sprache`='" . sql_escape($selected_language) . "' WHERE `UID`=" . sql_escape($user['UID']));
      $_SESSION['locale'] = $selected_language;
      
      success("Language changed.");
      redirect(page_link_to('user_settings'));
    }
  }
  
  return page_with_title(settings_title(), array(
      sprintf(_("Hello %s, here you can change your personal settings i.e. password, color settings etc."), User_Nick_render($user)),
      $msg,
      msg(),
      div('row', array(
          div('col-md-6', array(
              form(array(
                  form_info(_("Here you can change your user details.")),
                  form_text('nick', _("Nick") . "*", $nick),
                  form_text('lastname', _("Last name"), $lastname),
                  form_text('prename', _("First name"), $prename),
                  form_text('age', _("Age"), $age),
                  form_text('tel', _("Phone"), $tel),
                  form_text('dect', _("DECT"), $dect),
                  form_text('mobile', _("Mobile"), $mobile),
                  form_text('mail', _("E-Mail") . "*", $mail),
                  form_text('icq', _("ICQ"), $icq),
                  form_text('jabber', _("Jabber"), $jabber),
                  form_text('hometown', _("Hometown"), $hometown),
                  $enable_tshirt_size ? form_select('tshirt_size', _("Shirt size"), $tshirt_sizes, $tshirt_size) : '',
                  form_checkboxes('angel_types', _("What do you want to do?") . sprintf(" (<a href=\"%s\">%s</a>)", page_link_to('angeltypes') . '&action=about', _("Description of job types")), $angel_types, $selected_angel_types),
                  form_submit('submit', _("Save")) 
              )) 
          )),
          div('col-md-6', array(
              form(array(
                  form_info(_("Here you can change your password.")),
                  form_password('password', _("Old password:")),
                  form_password('new_password', _("New password:")),
                  form_password('new_password2', _("Password confirmation:")),
                  form_submit('submit_password', _("Save")) 
              )),
              form(array(
                  form_info(_("Here you can choose your color settings:")),
                  form_select('theme', _("Color settings:"), $themes, $selected_theme),
                  form_submit('submit_theme', _("Save")) 
              )),
              form(array(
                  form_info(_("Here you can choose your language:")),
                  form_select('language', _("Language:"), $locales, $selected_language),
                  form_submit('submit_language', _("Save")) 
              )) 
          )) 
      )) 
  ));
}
?>
