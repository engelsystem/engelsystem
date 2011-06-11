<?php


// Engel registrieren
function guest_register() {
	$html = "";
	$success = "none";

	if (isset ($_POST["send"])) {
		$eNick = trim($_POST["Nick"]);

		if ($_POST["Alter"] == "")
			$_POST["Alter"] = 23;

		// user vorhanden?
		$Ergans = sql_select("SELECT UID FROM `User` WHERE `Nick`='" . sql_escape($_POST["Nick"]) . "'");

		if (strlen($_POST["Nick"]) < 2)
			$error = Get_Text("makeuser_error_nick1")
				. $_POST["Nick"] . Get_Text("makeuser_error_nick2");

		elseif (count($Ergans) > 0)
			$error = Get_Text("makeuser_error_nick1")
				. $_POST["Nick"] . Get_Text("makeuser_error_nick3");

		elseif (strlen($_POST["email"]) <= 6 && strstr($_POST["email"], "@") == FALSE && strstr($_POST["email"], ".") == false)
			$error = Get_Text("makeuser_error_mail");

		elseif (!is_numeric($_POST["Alter"]))
			$error = Get_Text("makeuser_error_Alter");

		elseif ($_POST["Passwort"] != $_POST["Passwort2"])
			$error = Get_Text("makeuser_error_password1");

		elseif (strlen($_POST["Passwort"]) < 6)
			$error = Get_Text("makeuser_error_password2");

		else {
			$_POST["Passwort"] = PassCrypt($_POST["Passwort"]);
			unset ($_POST["Passwort2"]);

			$Erg = sql_query("INSERT INTO `User` (" .
			"`Nick` , " . "`Name` , " .
			"`Vorname`, " . "`Alter` , " .
			"`Telefon`, " . "`DECT`, " .
			"`Handy`, " . "`email`, " .
			"`ICQ`, " . "`jabber`, " .
			"`Size`, " . "`Passwort`, " .
			"`Art` , " . "`kommentar`, " .
			"`Hometown`," . "`CreateDate`, `Sprache` ) " .
			"VALUES ( '"
				. sql_escape($_POST["Nick"]) . "', " . "'"
				. sql_escape($_POST["Name"]) . "', " . "'"
				. sql_escape($_POST["Vorname"]) . "', " . "'"
				. sql_escape($_POST["Alter"]) . "', " . "'"
				. sql_escape($_POST["Telefon"]) . "', " . "'"
				. sql_escape($_POST["DECT"]) . "', " . "'"
				. sql_escape($_POST["Handy"]) . "', " . "'"
				. sql_escape($_POST["email"]) . "', " . "'"
				. sql_escape($_POST["ICQ"]) . "', " . "'"
				. sql_escape($_POST["jabber"]) . "', " . "'"
				. sql_escape($_POST["Size"]) . "', " . "'"
				. sql_escape($_POST["Passwort"]) . "', " . "'"
				. sql_escape($_POST["Art"]) . "', " . "'"
				. sql_escape($_POST["kommentar"]) . "', " . "'"
				. sql_escape($_POST["Hometown"]) . "',"
				. "NOW(), '"
				. sql_escape($_SESSION["Sprache"])
				. "')"
			);

			if ($Erg != 1) {
				$html .= Get_Text("makeuser_error_write1") . "<br />\n";
				$error = sql_error();
			} else {
				$html .= "<p class=\"success\">" . Get_Text("makeuser_writeOK") . "\n";

				$Erg3 = mysql_query("INSERT INTO `UserGroups` SET `uid`=" . sql_escape(sql_id()) . ", `group_id`=-2");

				if ($Erg3 != 1) {
					$html .= "<h1>" . Get_Text("makeuser_error_write2") . "<br />\n";
					$error = sql_error();
				} else {
					$html .= Get_Text("makeuser_writeOK2") . "<br />\n";
					$html .= "<h1>" . Get_Text("makeuser_writeOK3") . "</h1>\n";
				}

				$html .= Get_Text("makeuser_writeOK4") . "</p><p></p>\n<br /><br />\n";
				$success = "any";

				if (isset ($SubscribeMailinglist)) {
					if ($_POST["subscribe-mailinglist"] == "") {
						$headers = "From: " . $_POST["email"] . "\r\n" .
						"X-Mailer: PHP/" . phpversion();
						mail($SubscribeMailinglist, "subject", "message", $headers);
					}
				}
			}
		}

		if (isset ($error))
			$html .= error($error);
	} else {
		// init vars
		$_POST["Nick"] = "";
		$_POST["Name"] = "";
		$_POST["Vorname"] = "";
		$_POST["Alter"] = "";
		$_POST["Telefon"] = "";
		$_POST["DECT"] = "";
		$_POST["Handy"] = "";
		$_POST["email"] = "";
		$_POST["subscribe-mailinglist"] = "";
		$_POST["ICQ"] = "";
		$_POST["jabber"] = "";
		$_POST["Size"] = "L";
		$_POST["Art"] = "";
		$_POST["kommentar"] = "";
		$_POST["Hometown"] = "";
	}

	if ($success == "none") {
		$html .= "<h1>" . Get_Text("makeuser_text0") . "</h1>\n";
		$html .= "<h2>" . Get_Text("makeuser_text1") . "</h2>\n";
		$html .= "<form action=\"\" method=\"post\">\n";
		$html .= "<table>\n";
		$html .= "<tr><td>" . Get_Text("makeuser_Nickname") . "*</td><td><input type=\"text\" size=\"40\" name=\"Nick\" value=\"" . $_POST["Nick"] . "\" /></td></tr>\n";
		$html .= "<tr><td>" . Get_Text("makeuser_Nachname") . "</td><td><input type=\"text\" size=\"40\" name=\"Name\" value=\"" . $_POST["Name"] . "\" /></td></tr>\n";
		$html .= "<tr><td>" . Get_Text("makeuser_Vorname") . "</td><td><input type=\"text\" size=\"40\" name=\"Vorname\" value=\"" . $_POST["Vorname"] . "\" /></td></tr>\n";
		$html .= "<tr><td>" . Get_Text("makeuser_Alter") . "</td><td><input type=\"text\" size=\"40\" name=\"Alter\" value=\"" . $_POST["Alter"] . "\"></td></tr>\n";
		$html .= "<tr><td>" . Get_Text("makeuser_Telefon") . "</td><td><input type=\"text\" size=\"40\" name=\"Telefon\" value=\"" . $_POST["Telefon"] . "\"></td></tr>\n";
		$html .= "<tr><td>" . Get_Text("makeuser_DECT") . "</td><td><input type=\"text\" size=\"40\" name=\"DECT\" value=\"" . $_POST["DECT"] . "\"></td><td>\n";
		$html .= "<!--a href=\"https://21c3.ccc.de/wiki/index.php/POC\"><img src=\"./pic/external.png\" alt=\"external: \">DECT</a--></td></tr>\n";
		$html .= "<tr><td>" . Get_Text("makeuser_Handy") . "</td><td><input type=\"text\" size=\"40\" name=\"Handy\" value=\"" . $_POST["Handy"] . "\"></td></tr>\n";
		$html .= "<tr><td>" . Get_Text("makeuser_E-Mail") . "*</td><td><input type=\"text\" size=\"40\" name=\"email\" value=\"" . $_POST["email"] . "\"></td></tr>\n";

		if (isset ($SubscribeMailinglist))
			$html .= "<tr><td>" . Get_Text("makeuser_subscribe-mailinglist") . "</td><td><input type=\"checkbox\" name=\"subscribe-mailinglist\" value=\"" . $_POST["subscribe-mailinglist"] . "\">($SubscribeMailinglist)</td></tr>\n";

		$html .= "<tr><td>ICQ</td><td><input type=\"text\" size=\"40\" name=\"ICQ\" value=\"" . $_POST["ICQ"] . "\"></td></tr>\n";
		$html .= "<tr><td>jabber</td><td><input type=\"text\" size=\"40\" name=\"jabber\" value=\"" . $_POST["jabber"] . "\"></td></tr>\n";
		$html .= "<tr><td>" . Get_Text("makeuser_T-Shirt") . " Gr&ouml;sse*</td><td align=\"left\">\n";
		$html .= "<select name=\"Size\">\n";
		$html .= "<option value=\"S\"";
		if ($_POST["Size"] == "S")
			$html .= " selected";
		$html .= ">S</option>\n";
		$html .= "<option value=\"M\"";
		if ($_POST["Size"] == "M")
			$html .= " selected";
		$html .= ">M</option>\n";
		$html .= "<option value=\"L\"";
		if ($_POST["Size"] == "L")
			$html .= " selected";
		$html .= ">L</option>\n";
		$html .= "<option value=\"XL\"";
		if ($_POST["Size"] == "XL")
			$html .= " selected";
		$html .= ">XL</option>\n";
		$html .= "<option value=\"2XL\"";
		if ($_POST["Size"] == "2XL")
			$html .= " selected";
		$html .= ">2XL</option>\n";
		$html .= "<option value=\"3XL\"";
		if ($_POST["Size"] == "3XL")
			$html .= " selected";
		$html .= ">3XL</option>\n";
		$html .= "<option value=\"4XL\"";
		if ($_POST["Size"] == "4XL")
			$html .= " selected";
		$html .= ">4XL</option>\n";
		$html .= "<option value=\"5XL\"";
		if ($_POST["Size"] == "5XL")
			$html .= " selected";
		$html .= ">5XL</option>\n";
		$html .= "<option value=\"S-G\"";
		if ($_POST["Size"] == "S-G")
			$html .= " selected";
		$html .= ">S Girl</option>\n";
		$html .= "<option value=\"M-G\"";
		if ($_POST["Size"] == "M-G")
			$html .= " selected";
		$html .= ">M Girl</option>\n";
		$html .= "<option value=\"L-G\"";
		if ($_POST["Size"] == "L-G")
			$html .= " selected";
		$html .= ">L Girl</option>\n";
		$html .= "<option value=\"XL-G\"";
		if ($_POST["Size"] == "XL-G")
			$html .= " selected";
		$html .= ">XL Girl</option>\n";
		$html .= "</select>\n";
		$html .= "</td></tr>\n";
		$html .= "<tr><td>" . Get_Text("makeuser_Engelart") . "</td><td align=\"left\">\n";
		$html .= "<select name=\"Art\">\n";

		$engel_types = sql_select("SELECT * FROM `AngelTypes` ORDER BY `NAME`");
		foreach ($engel_types as $engel_type) {
			$Name = $engel_type['Name'] . Get_Text("inc_schicht_engel");
			$html .= "<option value=\"" . $Name . "\"";

			if ($_POST["Art"] == $Name)
				$html .= " selected";

			$html .= ">$Name</option>\n";
		}

		$html .= "</select>\n";
		$html .= "</td>\n";
		$html .= "</tr>\n";
		$html .= "<tr>\n";
		$html .= "<td>" . Get_Text("makeuser_text2") . "</td>\n";
		$html .= "<td><textarea rows=\"5\" cols=\"40\" name=\"kommentar\">" . $_POST["kommentar"] . "</textarea></td>\n";
		$html .= "</tr>\n";
		$html .= "<tr><td>" . Get_Text("makeuser_Hometown") . "</td><td><input type=\"text\" size=\"40\" name=\"Hometown\" value=\"" . $_POST["Hometown"] . "\"></td></tr>\n";
		$html .= "<tr><td>" . Get_Text("makeuser_Passwort") . "*</td><td><input type=\"password\" size=\"40\" name=\"Passwort\"/></td></tr>\n";
		$html .= "<tr><td>" . Get_Text("makeuser_Passwort2") . "*</td><td><input type=\"password\" size=\"40\" name=\"Passwort2\"/></td></tr>\n";
		$html .= "<tr><td>&nbsp;</td><td><input type=\"submit\" name=\"send\" value=\"" . Get_Text("makeuser_Anmelden") . "\"/></td></tr>\n";
		$html .= "</table>\n";
		$html .= "</form>\n";
		$html .= Get_Text("makeuser_text3");
	}
	return $html;
}

function guest_logout() {
	unset ($_SESSION['uid']);
	header("Location: " . page_link_to("start"));
}

function guest_login() {
	global $user;
	unset ($_SESSION['uid']);

	$html = "";
	if (isset ($_REQUEST['login_submit'])) {
		$login_user = sql_select("SELECT * FROM `User` WHERE `Nick`='" . sql_escape($_REQUEST["user"]) . "'");

		if (count($login_user) == 1) { // Check, ob User angemeldet wird...
			$login_user = $login_user[0];
			if ($login_user["Passwort"] == PassCrypt($_REQUEST["password"])) { // Passwort ok...
				$_SESSION['uid'] = $login_user['UID'];
				$_SESSION['Sprache'] = $login_user['Sprache'];
				header("Location: " . page_link_to("news"));
			} else { // Passwort nicht ok...
				$ErrorText = "pub_index_pass_no_ok";
			} // Ende Passwort-Check
		} else { // Anzahl der User in User-Tabelle <> 1 --> keine Anmeldung
			if ($user_anz == 0)
				$ErrorText = "pub_index_User_unset";
			else
				$ErrorText = "pub_index_User_more_as_one";
		} // Ende Check, ob User angemeldet wurde}
	}
	if (isset ($ErrorText))
		$html .= error(Get_Text($ErrorText));
	$html .= guest_login_form();
	return $html;
}

function guest_login_form() {
	return template_render("../templates/guest_login_form.html", array (
		'link' => page_link_to("login"),
		'nick' => Get_Text("index_lang_nick"),
		'pass' => Get_Text("index_lang_pass"),
		'send' => Get_Text("index_lang_send")
	));
}
?>
