<?php

// Testet ob ein User eingeloggt ist und lädt die entsprechenden Privilegien
function load_auth() {
  global $user, $privileges;

  $user = null;
  if (isset($_SESSION['uid'])) {
    $user = sql_select("SELECT * FROM `User` WHERE `UID`=" . sql_escape($_SESSION['uid']) . " LIMIT 1");
    if (count($user) > 0) {
      // User ist eingeloggt, Datensatz zur Verfügung stellen und Timestamp updaten
      list ($user) = $user;
      sql_query("UPDATE `User` SET " . "`lastLogIn` = '" . time() . "'" . " WHERE `UID` = '" . sql_escape($_SESSION['uid']) . "' LIMIT 1;");
    } else
      unset($_SESSION['uid']);
  }

  $privileges = isset($user) ? privileges_for_user($user['UID']) : privileges_for_group(- 1);
}

// generate a salt (random string) of arbitrary length suitable for the use with crypt()
function generate_salt($length = 16) {
  $alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
  $salt = "";
  for($i = 0; $i < $length; $i ++) {
    $salt .= $alphabet[rand(0, strlen($alphabet) - 1)];
  }
  return $salt;
}

// set the password of a user
function set_password($uid, $password) {
  return sql_query("UPDATE `User` SET `Passwort` = '" . sql_escape(crypt($password, CRYPT_ALG . '$' . generate_salt(16) . '$')) . "' WHERE `UID` = " . intval($uid) . " LIMIT 1");
}

// verify a password given a precomputed salt.
// if $uid is given and $salt is an old-style salt (plain md5), we convert it automatically
function verify_password($password, $salt, $uid = false) {
  $correct = false;
  if (substr($salt, 0, 1) == '$') // new-style crypt()
    $correct = crypt($password, $salt) == $salt;
  elseif (substr($salt, 0, 7) == '{crypt}') // old-style crypt() with DES and static salt - not used anymore
    $correct = crypt($password, '77') == $salt;
  elseif (strlen($salt) == 32) // old-style md5 without salt - not used anymore
    $correct = md5($password) == $salt;

  if ($correct && substr($salt, 0, strlen(CRYPT_ALG)) != CRYPT_ALG && $uid) {
    // this password is stored in another format than we want it to be.
    // let's update it!
    // we duplicate the query from the above set_password() function to have the extra safety of checking the old hash
    sql_query("UPDATE `User` SET `Passwort` = '" . sql_escape(crypt($password, CRYPT_ALG . '$' . generate_salt() . '$')) . "' WHERE `UID` = " . intval($uid) . " AND `Passwort` = '" . sql_escape($salt) . "' LIMIT 1");
  }
  return $correct;
}

// JSON Authorisierungs-Schnittstelle
function json_auth_service() {
  global $api_key;

  header("Content-Type: application/json");

  $User = $_REQUEST['user'];
  $Pass = $_REQUEST['pw'];
  $SourceOuth = $_REQUEST['so'];

  if (isset($api_key) && $SourceOuth == $api_key) {
    $sql = "SELECT `UID`, `Passwort` FROM `User` WHERE `Nick`='" . sql_escape($User) . "'";
    $Erg = sql_select($sql);

    if (count($Erg) == 1) {
      $Erg = $Erg[0];
      if (verify_password($Pass, $Erg["Passwort"], $Erg["UID"])) {
        $user_privs = sql_select("SELECT `Privileges`.`name` FROM `User` JOIN `UserGroups` ON (`User`.`UID` = `UserGroups`.`uid`) JOIN `GroupPrivileges` ON (`UserGroups`.`group_id` = `GroupPrivileges`.`group_id`) JOIN `Privileges` ON (`GroupPrivileges`.`privilege_id` = `Privileges`.`id`) WHERE `User`.`UID`=" . sql_escape($UID) . ";");
        foreach ($user_privs as $user_priv)
          $privileges[] = $user_priv['name'];

        $msg = array (
            'status' => 'success',
            'rights' => $privileges
        );
        echo json_encode($msg);
        die();
      }
    }
  }

  echo json_encode(array (
      'status' => 'failed',
      'error' => "JSON Service GET syntax: https://engelsystem.de/?auth&user=<user>&pw=<password>&so=<key>, POST is possible too"
  ));
  die();
}

function privileges_for_user($user_id) {
  $privileges = array ();
  $user_privs = sql_select("SELECT `Privileges`.`name` FROM `User` JOIN `UserGroups` ON (`User`.`UID` = `UserGroups`.`uid`) JOIN `GroupPrivileges` ON (`UserGroups`.`group_id` = `GroupPrivileges`.`group_id`) JOIN `Privileges` ON (`GroupPrivileges`.`privilege_id` = `Privileges`.`id`) WHERE `User`.`UID`=" . sql_escape($user_id) . ";");
  foreach ($user_privs as $user_priv)
    $privileges[] = $user_priv['name'];
  return $privileges;
}

function privileges_for_group($group_id) {
  $privileges = array ();
  $groups_privs = sql_select("SELECT * FROM `GroupPrivileges` JOIN `Privileges` ON (`GroupPrivileges`.`privilege_id` = `Privileges`.`id`) WHERE `group_id`=" . sql_escape($group_id));
  foreach ($groups_privs as $guest_priv)
    $privileges[] = $guest_priv['name'];
  return $privileges;
}
?>
