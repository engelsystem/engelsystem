<?php


// Testet ob ein User eingeloggt ist und lädt die entsprechenden Privilegien
function load_auth() {
	global $user, $privileges;

	if (!isset ($_SESSION['IP']))
		$_SESSION['IP'] = $_SERVER['REMOTE_ADDR'];

	if ($_SESSION['IP'] != $_SERVER['REMOTE_ADDR']) {
		session_destroy();
		error("Your session has been destroyed because your ip-address changed.");
		header("Location: " . page_link_to('start'));
	}

	$user = null;
	if (isset ($_SESSION['uid'])) {
		$user = sql_select("SELECT * FROM `User` WHERE `UID`=" . sql_escape($_SESSION['uid']) . " LIMIT 1");
		if (count($user) > 0) {
			// User ist eingeloggt, Datensatz zur Verfügung stellen und Timestamp updaten
			list ($user) = $user;
			sql_query("UPDATE `User` SET " . "`lastLogIn` = '" . time() . "'" . " WHERE `UID` = '" . sql_escape($_SESSION['uid']) . "' LIMIT 1;");
		} else
			unset ($_SESSION['uid']);
	}

	$privileges = isset ($user) ? privileges_for_user($user['UID']) : privileges_for_group(-1);
}

function PassCrypt($passwort) {
	global $crypt_system;

	switch ($crypt_system) {
		case "crypt" :
			return "{crypt}" . crypt($passwort, "77");
		case "md5" :
			return md5($passwort);
	}
}

// JSON Authorisierungs-Schnittstelle
function json_auth_service() {
	global $CurrentExternAuthPass;

	header("Content-Type: application/json");

	$User = $_REQUEST['user'];
	$Pass = $_REQUEST['pw'];
	$SourceOuth = $_REQUEST['so'];

	if (isset ($CurrentExternAuthPass) && $SourceOuth == $CurrentExternAuthPass) {
		$sql = "SELECT * FROM `User` WHERE `Nick`='" . sql_escape($User) . "'";
		$Erg = sql_query($sql);

		if (mysql_num_rows($Erg) == 1) {
			if (mysql_result($Erg, 0, "Passwort") == PassCrypt($Pass)) {
				$UID = mysql_result($Erg, 0, "UID");

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
