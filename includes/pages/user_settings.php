<?php
function user_settings() {
	global $enable_tshirt_size, $tshirt_sizes, $themes, $languages;
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
	$selected_angel_types = array ();
	foreach ($selected_angel_types_source as $selected_angel_type)
		$selected_angel_types[] = $selected_angel_type['angeltype_id'];

	$angel_types_source = sql_select("SELECT * FROM `AngelTypes` ORDER BY `name`");
	$angel_types = array ();
	foreach ($angel_types_source as $angel_type)
		$angel_types[$angel_type['id']] = $angel_type['name'] . ($angel_type['restricted'] ? " (restricted)" : "");

	if (isset ($_REQUEST['submit'])) {
		$ok = true;

		if (isset ($_REQUEST['nick']) && strlen(strip_request_item('nick')) > 1) {
			$nick = strip_request_item('nick');
			if (sql_num_query("SELECT * FROM `User` WHERE `Nick`='" . sql_escape($nick) . "' AND NOT `UID`=" . sql_escape($user['UID']) . " LIMIT 1") > 0) {
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

		if (isset ($_REQUEST['tshirt_size']) && isset ($tshirt_sizes[$_REQUEST['tshirt_size']]))
			$tshirt_size = $_REQUEST['tshirt_size'];
		else {
			$ok = false;
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

		if ($ok) {
			sql_query("UPDATE `User` SET `Nick`='" . sql_escape($nick) . "', `Vorname`='" . sql_escape($prename) . "', `Name`='" . sql_escape($lastname) .
			"', `Alter`='" . sql_escape($age) . "', `Telefon`='" . sql_escape($tel) . "', `DECT`='" . sql_escape($dect) . "', `Handy`='" . sql_escape($mobile) .
			"', `email`='" . sql_escape($mail) . "', `ICQ`='" . sql_escape($icq) . "', `jabber`='" . sql_escape($jabber) . "', `Size`='" . sql_escape($tshirt_size) .
			"', `Hometown`='" . sql_escape($hometown) . "' WHERE `UID`=" . sql_escape($user['UID']));

			// Assign angel-types
			foreach ($angel_types_source as $angel_type)
				if (!in_array($angel_type['id'], $selected_angel_types))
					sql_query("DELETE FROM `UserAngelTypes` WHERE `user_id`=" . sql_escape($user['UID']) . " AND `angeltype_id`=" . sql_escape($angel_type['id']) . " LIMIT 1");

			foreach ($selected_angel_types as $selected_angel_type_id)
				if (sql_num_query("SELECT * FROM `UserAngelTypes` WHERE `user_id`=" . sql_escape($user['UID']) . " AND `angeltype_id`=" . sql_escape($selected_angel_type_id) . " LIMIT 1") == 0)
					sql_query("INSERT INTO `UserAngelTypes` SET `user_id`=" . sql_escape($user['UID']) . ", `angeltype_id`=" . sql_escape($selected_angel_type_id));

			success("Settings saved.");
			redirect(page_link_to('user_settings'));
		}
	}
	elseif (isset ($_REQUEST['submit_password'])) {
		$ok = true;

		if (!isset ($_REQUEST['password']) || $user['Passwort'] != PassCrypt($_REQUEST['password'])) {
			$ok = false;
			$msg .= error(Get_Text(30), true);
		}

		if (isset ($_REQUEST['new_password']) && strlen($_REQUEST['new_password']) >= 6) {
			if ($_REQUEST['new_password'] == $_REQUEST['new_password2']) {
				$password_hash = PassCrypt($_REQUEST['new_password']);
			} else {
				$ok = false;
				$msg .= error(Get_Text("makeuser_error_password1"), true);
			}
		} else {
			$ok = false;
			$msg .= error(Get_Text("makeuser_error_password2"), true);
		}

		if ($ok) {
			sql_query("UPDATE `User` SET `Passwort`='" . sql_escape($password_hash) . "' WHERE `UID`=" . sql_escape($user['UID']));

			success("Password saved.");
			redirect(page_link_to('user_settings'));
		}
	}
	elseif (isset ($_REQUEST['submit_theme'])) {
		$ok = true;

		if (isset ($_REQUEST['theme']) && isset ($themes[$_REQUEST['theme']]))
			$selected_theme = $_REQUEST['theme'];
		else
			$ok = false;

		if ($ok) {
			sql_query("UPDATE `User` SET `color`='" . sql_escape($selected_theme) . "' WHERE `UID`=" . sql_escape($user['UID']));

			success("Theme changed.");
			redirect(page_link_to('user_settings'));
		}
	}
	elseif (isset ($_REQUEST['submit_language'])) {
		$ok = true;

		if (isset ($_REQUEST['language']) && isset ($languages[$_REQUEST['language']]))
			$selected_language = $_REQUEST['language'];
		else
			$ok = false;

		if ($ok) {
			sql_query("UPDATE `User` SET `Sprache`='" . sql_escape($selected_language) . "' WHERE `UID`=" . sql_escape($user['UID']));
			$_SESSION['Sprache'] = $selected_language;

			success("Language changed.");
			redirect(page_link_to('user_settings'));
		}
	}

	return page(array (
		sprintf(Get_Text("Hallo") . "%s,<br />" . Get_Text(13), $user['Nick']),
		$msg,
		msg(),
		form(array (
			form_info("", Get_Text("pub_einstellungen_Text_UserData")),
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
			form_checkboxes('angel_types', "What do you want to do?", $angel_types, $selected_angel_types),
			form_submit('submit', Get_Text("save"))
		)),
		form(array (
			form_info("", Get_Text(14)),
			form_password('password', Get_Text(15)),
			form_password('new_password', Get_Text(16)),
			form_password('new_password2', Get_Text(17)),
			form_submit('submit_password', Get_Text("save"))
		)),
		form(array (
			form_info("", Get_Text(18)),
			form_select('theme', Get_Text(19), $themes, $selected_theme),
			form_submit('submit_theme', Get_Text("save"))
		)),
		form(array (
			form_info("", Get_Text(20)),
			form_select('language', Get_Text(21), $languages, $selected_language),
			form_submit('submit_language', Get_Text("save"))
		))
	));
}
?>
