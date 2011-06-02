<?php
function user_settings() {
	global $user;

	if (!isset ($_REQUEST['action'])) {
		$tshirt_html = template_render('../templates/user_settings_tshirt.html', array (
			'label_size' => Get_Text("makeuser_T-Shirt"),
			'size_select' => ($user['Tshirt'] == 0) ? html_select_key('size', array (
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
			'theme_select' => html_select_key('theme', array (
				"1" => "Standard-Style",
				"2" => "ot/Gelber Style",
				"3" => "Club-Mate Style",
				"5" => "Debian Style",
				"6" => "c-base Style",
				"7" => "Blau/Gelber Style",
				"8" => "Pastel Style",
				"4" => "Test Style",
				"9" => "Test Style 21c3",
				"10" => "msquare (cccamp2011)"
			), $user['color']),
			'text_language' => Get_Text(20),
			'language_label' => Get_Text(21),
			'language_select' => html_select_key('language', array (
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
						$html .= error(Get_Text(30));
					}
				} else {
					$html .= error(Get_Text(31));
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
	
		switch (GetPicturShow($_SESSION['UID'])) {
			case 'Y' :
				echo Get_Text('pub_einstellungen_PictureShow') . "<br />";
				echo displayPictur($_SESSION['UID'], 0);
				echo "<form action=\"./einstellungen.php\" method=\"post\">\n";
				echo "<input type=\"hidden\" name=\"action\" value=\"delPicture\">\n";
				echo "<input type=\"submit\" value=\"" . Get_Text("delete"), "\">\n";
				echo "</form>\n";
				break;
			case 'N' :
				echo Get_Text('pub_einstellungen_PictureNoShow') . "<br />";
				echo displayPictur($_SESSION['UID'], 0);
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
	
						if (GetPicturShow($_SESSION['UID']) == "")
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
