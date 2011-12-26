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
			sql_query("DELETE FROM `UserAngelTypes` WHERE `user_id`=" . sql_escape($user['UID']));
			foreach ($selected_angel_types as $selected_angel_type_id)
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

	if (!isset ($_REQUEST['action'])) {
		if ($enable_tshirt_size)
			$tshirt_html = template_render('../templates/user_settings_tshirt.html', array (
				'label_size' => Get_Text("makeuser_T-Shirt"),
				'size_select' => ($user['Tshirt'] == 0) ? html_select_key('size', 'size', array (
					'S' => "S",
					'M' => "M",
					'L' => "L",
					'XL' => "XL",
					'2XL' => "2XL",
					'3XL' => "3XL",
					'4XL' => "4XL",
					'5XL' => "5XL",
					'S-G' => "S Girl",
					'M-G' => "M Girl",
					'L-G' => "L Girl",
					'XL-G' => "XL Girl"
				), $user['Size']) : $user['Size']
			));
		else
			$tshirt_html = "";

		return template_render('../templates/user_settings.html', array (
			'link' => page_link_to("user_settings"),
			'greeting' => Get_Text("Hallo") . $user['Nick'] . ",<br />" . Get_Text(13),
			'text_user_data' => Get_Text("pub_einstellungen_Text_UserData"),
			'label_nick' => Get_Text("pub_einstellungen_Nick"),
			'label_name' => Get_Text("pub_einstellungen_Name"),
			'label_prename' => Get_Text("pub_einstellungen_Vorname"),
			'label_age' => Get_Text("pub_einstellungen_Alter"),
			'label_tel' => Get_Text("pub_einstellungen_Telefon"),
			'label_mobile' => Get_Text("pub_einstellungen_Handy"),
			'label_dect' => Get_Text("pub_einstellungen_DECT"),
			'label_mail' => Get_Text("pub_einstellungen_Email"),
			'label_hometown' => Get_Text("pub_einstellungen_Hometown"),
			'nick' => $user['Nick'],
			'name' => $user['Name'],
			'prename' => $user['Vorname'],
			'age' => $user['Alter'],
			'tel' => $user['Telefon'],
			'mobile' => $user['Handy'],
			'dect' => $user['DECT'],
			'mail' => $user['email'],
			'icq' => $user['ICQ'],
			'jabber' => $user['jabber'],
			'hometown' => $user['Hometown'],
			'label_save' => Get_Text("save"),
			'tshirts' => $tshirt_html,
			'text_password' => Get_Text(14),
			'current_pw_label' => Get_Text(15),
			'new_pw_label' => Get_Text(16),
			'new_pw2_label' => Get_Text(17),
			'text_theme' => Get_Text(18),
			'theme_label' => Get_Text(19),
			'theme_select' => html_select_key('theme', 'theme', array (
				"1" => "Standard-Style",
				"2" => "ot/Gelber Style",
				"3" => "Club-Mate Style",
				"5" => "Debian Style",
				"6" => "c-base Style",
				"7" => "Blau/Gelber Style",
				"8" => "Pastel Style",
				"4" => "Test Style",
				"9" => "Test Style 21c3",
				"10" => "msquare (cccamp2011)",
				"11" => "msquare (28c3)"
			), $user['color']),
			'text_language' => Get_Text(20),
			'language_label' => Get_Text(21),
			'language_select' => html_select_key('language', 'language', array (
				'DE' => "Deutsch",
				'EN' => "English"
			), $user['Sprache'])
		));
	} else {
		switch ($_REQUEST['action']) {
			case 'sprache' :
				if (isset ($_REQUEST['language']) && preg_match("/^DE|EN$/", $_REQUEST['language']))
					$language = $_REQUEST['language'];
				else
					$language = "EN";
				sql_query("UPDATE `User` SET " . "`Sprache`='" . sql_escape($language) . "' WHERE `UID`=" . sql_escape($user['UID']) . " LIMIT 1");
				$_SESSION['Sprache'] = $language;
				header("Location: " . page_link_to("user_settings"));
				break;

			case 'colour' :
				$theme = preg_replace("/([^0-9]{1,})/ui", '', strip_tags($_REQUEST['theme']));
				sql_query("UPDATE `User` SET " . "`color`='" . sql_escape($theme) . "' WHERE `UID`=" . sql_escape($user['UID']) . " LIMIT 1");
				header("Location: " . page_link_to("user_settings"));
				break;

			case 'set' :
				$html = "";
				if ($_REQUEST["new_pw"] == $_REQUEST["new_pw2"]) {
					if (PassCrypt($_REQUEST["current_pw"]) == $user['Passwort']) {
						sql_query("UPDATE `User` SET `Passwort`='" . sql_escape(PassCrypt($_REQUEST['new_pw'])) . "' WHERE `UID`=" . sql_escape($user['UID']) . " LIMIT 1");
						header("Location: " . page_link_to("user_settings"));
					} else {
						$html .= error(Get_Text(30), true);
					}
				} else {
					$html .= error(Get_Text(31), true);
				}
				return $html;
				break;

			case "setUserData" :
				$nick = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui", '', strip_tags($_REQUEST['nick']));
				$name = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui", '', strip_tags($_REQUEST['name']));
				$prename = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui", '', strip_tags($_REQUEST['prename']));
				$age = preg_replace("/([^0-9]{1,})/ui", '', strip_tags($_REQUEST['age']));
				$tel = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui", '', strip_tags($_REQUEST['tel']));
				$mobile = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui", '', strip_tags($_REQUEST['mobile']));
				$dect = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui", '', strip_tags($_REQUEST['dect']));
				$mail = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui", '', strip_tags($_REQUEST['mail']));
				$icq = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui", '', strip_tags($_REQUEST['icq']));
				$jabber = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui", '', strip_tags($_REQUEST['jabber']));
				$hometown = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui", '', strip_tags($_REQUEST['hometown']));
				$size = ($user['TShirt'] == 0) ? preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui", '', strip_tags($_REQUEST['size'])) : $user['Size'];

				sql_query("UPDATE `User` SET " .
				"`Nick`='" . sql_escape($nick) . "', " .
				"`Name`='" . sql_escape($name) . "', " .
				"`Vorname`='" . sql_escape($prename) . "', " .
				"`Alter`='" . sql_escape($age) . "', " .
				"`Telefon`='" . sql_escape($tel) . "', " .
				"`Handy`='" . sql_escape($mobile) . "', " .
				"`DECT`='" . sql_escape($dect) . "', " .
				"`email`='" . sql_escape($mail) . "', " .
				"`ICQ`='" . sql_escape($icq) . "', " .
				"`jabber`='" . sql_escape($jabber) . "', " .
				"`Hometown`='" . sql_escape($hometown) . "', " .
				"`Size`='" . sql_escape($size) . "' " .
				"WHERE `UID`=" . sql_escape($user['UID']) . " LIMIT 1");
				header("Location: " . page_link_to("user_settings"));
				break;
		}
	}

	// AVATARE
	/*
		if (get_cfg_var("file_uploads")) {
			echo "<br />\n<hr width=\"100%\">\n<br />\n\n";
			echo Get_Text('pub_einstellungen_PictureUpload') . "<br />";
			echo "<form action=\"./einstellungen.php\" method=\"post\" enctype=\"multipart/form-data\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"sendPicture\">\n";
			echo "<input name=\"file\" type=\"file\" size=\"50\" maxlength=\"" . get_cfg_var("post_max_size") . "\">\n";
			echo "(max " . get_cfg_var("post_max_size") . "Byte)<br />\n";
			echo "<input type=\"submit\" value=\"" . Get_Text("upload"), "\">\n";
			echo "</form>\n";
		}
	
		switch (GetPictureShow($_SESSION['UID'])) {
			case 'Y' :
				echo Get_Text('pub_einstellungen_PictureShow') . "<br />";
				echo displayPicture($_SESSION['UID'], 0);
				echo "<form action=\"./einstellungen.php\" method=\"post\">\n";
				echo "<input type=\"hidden\" name=\"action\" value=\"delPicture\">\n";
				echo "<input type=\"submit\" value=\"" . Get_Text("delete"), "\">\n";
				echo "</form>\n";
				break;
			case 'N' :
				echo Get_Text('pub_einstellungen_PictureNoShow') . "<br />";
				echo displayPicture($_SESSION['UID'], 0);
				echo "<form action=\"./einstellungen.php\" method=\"post\">\n";
				echo "<input type=\"hidden\" name=\"action\" value=\"delPicture\">\n";
				echo "<input type=\"submit\" value=\"" . Get_Text("delete"), "\">\n";
				echo "</form>\n";
				echo "<br />\n<hr width=\"100%\">\n<br />\n\n";
			case '' :
				echo "<br />\n<hr width=\"100%\">\n<br />\n\n";
				echo Get_Text(22) . "<br />";
				echo "\n<form action=\"./einstellungen.php\" method=\"post\">\n";
				echo "<input type=\"hidden\" name=\"action\" value=\"avatar\">\n";
				echo "<table>\n";
				echo "<tr>\n<td>" . Get_Text(23) . "<br /></td>\n</tr>\n";
				echo "<tr>\n";
				echo "<td>\n";
				echo "<select name=\"eAvatar\" onChange=\"document.avatar.src = '" . $url . $ENGEL_ROOT . "pic/avatar/avatar' + this.value  + '.gif'\" onKeyup=\"document.avatar.src = '" . $url . $ENGEL_ROOT . "pic/avatar/avatar' + this.value  + '.gif'\">\n";
	
				for ($i = 1; file_exists("../pic/avatar/avatar" . $i . ".gif"); $i++)
					echo "<option value=\"" . $i . "\"" . ($_SESSION['Avatar'] == $i ? " selected" : "") . ">avatar" . $i . "</option>\n";
	
				echo "</select>&nbsp;&nbsp;\n";
				echo "<img src=\"" . $url . $ENGEL_ROOT . "pic/avatar/avatar" . $_SESSION['Avatar'] . ".gif\" name=\"avatar\" border=\"0\" align=\"top\">\n";
				echo "</td>\n</tr>\n";
				echo "</table>\n";
				echo "<input type=\"submit\" value=\"" . Get_Text("save") . "\">\n";
				echo "</form>\n";
				break;
		} //CASE
	
	} else {
		switch ($_POST["action"]) {
	
			case 'avatar' :
				$chsql = "UPDATE `User` SET `Avatar`='" . $_POST["eAvatar"] . "' WHERE `UID`='" . $_SESSION['UID'] . "' LIMIT 1";
				$Erg = mysql_query($chsql, $con);
				$_SESSION['Avatar'] = $_POST["eAvatar"];
				if ($Erg == 1)
					Print_Text(34);
				else
					Print_Text(29);
				break;
	
			case 'setUserData' :
	
				break;
	
			case 'sendPicture' :
				if ($_FILES["file"]["size"] > 0) {
					if (($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/png") || ($_FILES["file"]["type"] == "image/gif")) {
						$data = addslashes(fread(fopen($_FILES["file"]["tmp_name"], "r"), filesize($_FILES["file"]["tmp_name"])));
	
						if (GetPictureShow($_SESSION['UID']) == "")
							$SQL = "INSERT INTO `UserPicture` " .
							"( `UID`,`Bild`, `ContentType`, `show`) " .
							"VALUES ('" . $_SESSION['UID'] . "', '$data', '" . $_FILES["file"]["type"] . "', 'N')";
						else
							$SQL = "UPDATE `UserPicture` SET " .
							"`Bild`='$data', " .
							"`ContentType`='" . $_FILES["file"]["type"] . "', " .
							"`show`='N' " .
							"WHERE `UID`='" . $_SESSION['UID'] . "'";
	
						$res = mysql_query($SQL, $con);
						if ($res)
							Print_Text("pub_einstellungen_send_OK");
						else
							Print_Text("pub_einstellungen_send_KO");
	
						echo "<h6>('" . $_FILES["file"]["name"] . "', MIME-Type: " . $_FILES["file"]["type"] . ", " . $_FILES["file"]["size"] . " Byte)</h6>";
					} else
						Print_Text("pub_einstellungen_send_KO");
				} else
					Print_Text("pub_einstellungen_send_KO");
				break;
	
			case 'delPicture' :
				$chsql = "DELETE FROM `UserPicture` WHERE `UID`='" . $_SESSION['UID'] . "' LIMIT 1";
				$Erg = mysql_query($chsql, $con);
				if ($Erg == 1)
					Print_Text("pub_einstellungen_del_OK");
				else
					Print_Text("pub_einstellungen_del_KO");
				Break;
		}
	}
	*/
}
?>
