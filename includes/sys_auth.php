<?php


// Testet ob ein User eingeloggt ist und lädt die entsprechenden Privilegien
function load_auth() {
	global $user;

	if (!isset ($_SESSION['IP']))
		$_SESSION['IP'] = $_SERVER['REMOTE_ADDR'];

	if ($_SESSION['IP'] != $_SERVER['REMOTE_ADDR']) {
		session_destroy();
		header("Location: " . link_to_page($start));
	}

	$user = null;
	if (isset ($_SESSION['uid'])) {
		$user = sql_select("SELECT * FROM `User` WHERE `UID`=" . sql_escape($_SESSION['uid']) . " LIMIT 1");
		if (count($user) > 0) {
			// User ist eingeloggt, Datensatz zur Verfügung stellen und Timestamp updaten
			list ($user) = $user;
			sql_query("UPDATE `User` SET "
				. "`lastLogIn` = '" . time() . "'"
				. " WHERE `UID` = '" . sql_escape($_SESSION['uid']) . "' LIMIT 1;"
			);
		} else
			unset ($_SESSION['uid']);
	}

	load_privileges();
}

function load_privileges() {
	global $privileges, $user;

	$privileges = array ();
	if (isset ($user)) {
		$user_privs = sql_select("SELECT `Privileges`.`name` FROM `User` JOIN `UserGroups` ON (`User`.`UID` = `UserGroups`.`uid`) JOIN `GroupPrivileges` ON (`UserGroups`.`group_id` = `GroupPrivileges`.`group_id`) JOIN `Privileges` ON (`GroupPrivileges`.`privilege_id` = `Privileges`.`id`) WHERE `User`.`UID`=" . sql_escape($user['UID']) . ";");
		foreach ($user_privs as $user_priv)
			$privileges[] = $user_priv['name'];
	} else {
		$guest_privs = sql_select("SELECT * FROM `GroupPrivileges` JOIN `Privileges` ON (`GroupPrivileges`.`privilege_id` = `Privileges`.`id`) WHERE `group_id`=-1;");
		foreach ($guest_privs as $guest_priv)
			$privileges[] = $guest_priv['name'];
	}
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
?>
