<?php


// Engel registrieren
function guest_register() {
  global $tshirt_sizes, $enable_tshirt_size;

  $msg = "";
  $nick = "";
  $lastname = "";
  $prename = "";
  $age = 23;
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
  $selected_angel_types = array ();

  $angel_types_source = sql_select("SELECT * FROM `AngelTypes` ORDER BY `name`");
  $angel_types = array ();
  foreach ($angel_types_source as $angel_type)
    $angel_types[$angel_type['id']] = $angel_type['name'] . ($angel_type['restricted'] ? " (restricted)" : "");

  if (isset ($_REQUEST['submit'])) {
    $ok = true;

    if (isset ($_REQUEST['nick']) && strlen(strip_request_item('nick')) > 1) {
      $nick = strip_request_item('nick');
      if (sql_num_query("SELECT * FROM `User` WHERE `Nick`='" . sql_escape($nick) . "' LIMIT 1") > 0) {
        $ok = false;
        $msg .= error(sprintf(Get_Text("makeuser_error_nick1") . "%s" . Get_Text("makeuser_error_nick3"), $nick), true);
      }
    } else {
      $ok = false;
      $msg .= error(sprintf(Get_Text("makeuser_error_nick1") . "%s" . Get_Text("makeuser_error_nick2"), strip_request_item('nick')), true);
    }

    if (isset ($_REQUEST['mail']) && strlen(strip_request_item('mail')) > 0) {
      $mail = strip_request_item('mail');
      if (!check_email($mail)) {
        $ok = false;
        $msg .= error(Get_Text("makeuser_error_mail"), true);
      }
    } else {
      $ok = false;
      $msg .= error("Please enter your e-mail.", true);
    }

    if (isset ($_REQUEST['icq']))
      $icq = strip_request_item('icq');
    if (isset ($_REQUEST['jabber']) && strlen(strip_request_item('jabber')) > 0) {
      $jabber = strip_request_item('jabber');
      if (!check_email($jabber)) {
        $ok = false;
        $msg .= error("Please check your jabber.", true);
      }
    }

    if ($enable_tshirt_size) {
      if (isset ($_REQUEST['tshirt_size']) && isset ($tshirt_sizes[$_REQUEST['tshirt_size']]) && $_REQUEST['tshirt_size'] != '')
        $tshirt_size = $_REQUEST['tshirt_size'];
      else {
        $ok = false;
        $msg .= error("Please select your shirt size.", true);
      }
    }

    if (isset ($_REQUEST['password']) && strlen($_REQUEST['password']) >= MIN_PASSWORD_LENGTH) {
      if ($_REQUEST['password'] != $_REQUEST['password2']) {
        $ok = false;
        $msg .= error(Get_Text("makeuser_error_password1"), true);
      }
    } else {
      $ok = false;
      $msg .= error(Get_Text("makeuser_error_password2"), true);
    }

    $selected_angel_types = array ();
    foreach ($angel_types as $angel_type_id => $angel_type_name)
      if (isset ($_REQUEST['angel_types_' . $angel_type_id]))
      $selected_angel_types[] = $angel_type_id;

    // Trivia
    if (isset ($_REQUEST['lastname']))
      $lastname = strip_request_item('lastname');
    if (isset ($_REQUEST['prename']))
      $prename = strip_request_item('prename');
    if (isset ($_REQUEST['age']) && preg_match("/^[0-9]{0,4}$/", $_REQUEST['age']))
      $age = strip_request_item('age');
    if (isset ($_REQUEST['tel']))
      $tel = strip_request_item('tel');
    if (isset ($_REQUEST['dect']))
      $dect = strip_request_item('dect');
    if (isset ($_REQUEST['mobile']))
      $mobile = strip_request_item('mobile');
    if (isset ($_REQUEST['hometown']))
      $hometown = strip_request_item('hometown');
    if (isset ($_REQUEST['comment']))
      $comment = strip_request_item_nl('comment');

    if ($ok) {
      sql_query("INSERT INTO `User` SET `Nick`='" . sql_escape($nick) . "', `Vorname`='" . sql_escape($prename) . "', `Name`='" . sql_escape($lastname) .
          "', `Alter`='" . sql_escape($age) . "', `Telefon`='" . sql_escape($tel) . "', `DECT`='" . sql_escape($dect) . "', `Handy`='" . sql_escape($mobile) .
          "', `email`='" . sql_escape($mail) . "', `ICQ`='" . sql_escape($icq) . "', `jabber`='" . sql_escape($jabber) . "', `Size`='" . sql_escape($tshirt_size) .
          "', `Passwort`='" . sql_escape($password_hash) . "', `kommentar`='" . sql_escape($comment) . "', `Hometown`='" . sql_escape($hometown) . "', `CreateDate`=NOW(), `Sprache`='" . sql_escape($_SESSION["Sprache"]) . "'");

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
      success(Get_Text("makeuser_writeOK4"));
      //if (!isset ($_SESSION['uid']))
      redirect(page_link_to('login'));
    }
  }

  return page(array (
    Get_Text("makeuser_text1"),
    $msg,
    msg(),
    form(array (
      form_text('nick', Get_Text("makeuser_Nickname") . "*", $nick),
      form_text('lastname', Get_Text("makeuser_Nachname"), $lastname),
      form_text('prename', Get_Text("makeuser_Vorname"), $prename),
      form_text('age', Get_Text("makeuser_Alter"), $age),
      form_text('tel', Get_Text("makeuser_Telefon"), $tel),
      form_text('dect', Get_Text("makeuser_DECT"), $tel),
      form_text('mobile', Get_Text("makeuser_Handy"), $mobile),
      form_text('mail', Get_Text("makeuser_E-Mail") . "*", $mail),
      form_text('icq', "ICQ", $icq),
      form_text('jabber', "Jabber", $jabber),
      form_text('hometown', Get_Text("makeuser_Hometown"), $hometown),
      $enable_tshirt_size ? form_select('tshirt_size', Get_Text("makeuser_T-Shirt"), $tshirt_sizes, $tshirt_size) : '',
      form_textarea('comment', Get_Text("makeuser_text2"), $comment),
      form_checkboxes('angel_types', "What do you want to do?<br>(<a href=\"https://events.ccc.de/congress/2012/wiki/Volunteers#What_kind_of_volunteers_are_needed.3F\">Description of job types</a>)", $angel_types, $selected_angel_types),
      form_info("", "Restricted angel types need will be confirmed later by an archangel. You can change your selection in the options section."),
      form_password('password', Get_Text("makeuser_Passwort") . "*"),
      form_password('password2', Get_Text("makeuser_Passwort2") . "*"),
      info(Get_Text("makeuser_text3"), true),
      form_submit('submit', Get_Text("makeuser_Anmelden"))
    ))
  ));
}

function guest_logout() {
  session_destroy();
  header("Location: " . page_link_to("start"));
}

function guest_login() {
  global $user;

  $msg = "";
  $nick = "";

  unset ($_SESSION['uid']);

  if (isset ($_REQUEST['submit'])) {
    $ok = true;

    if (isset ($_REQUEST['nick']) && strlen(strip_request_item('nick')) > 0) {
      $nick = strip_request_item('nick');
      $login_user = sql_select("SELECT * FROM `User` WHERE `Nick`='" . sql_escape($nick) . "'");
      if (count($login_user) > 0) {
        $login_user = $login_user[0];
        if (isset ($_REQUEST['password'])) {
          if (!verify_password($_REQUEST['password'], $login_user['Passwort'], $login_user['UID'])) {
            $ok = false;
            $msg .= error(Get_Text("pub_index_pass_no_ok"), true);
          }
        } else {
          $ok = false;
          $msg .= error("Please enter a password.", true);
        }
      } else {
        $ok = false;
        $msg .= error(Get_Text("pub_index_User_unset"), true);
      }
    } else {
      $ok = false;
      $msg .= error("Please enter a nickname.", true);
    }

    if ($ok) {
      $_SESSION['uid'] = $login_user['UID'];
      $_SESSION['Sprache'] = $login_user['Sprache'];
      redirect(page_link_to('news'));
    }
  }

  return page(array (
    $msg,
    msg(),
    Get_Text("index_text1") . " " . Get_Text("index_text2") . " " . Get_Text("index_text3"),
    form(array (
      form_text('nick', Get_Text("index_lang_nick"), $nick),
      form_password('password', Get_Text("index_lang_pass")),
      form_submit('submit', Get_Text("index_lang_send"))
    )),
    info(Get_Text("index_text4"), true)
  ));
}
?>
