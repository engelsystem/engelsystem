<?php
function admin_user() {
	global $user;

	include ("includes/funktion_db_list.php");

	$html = "";

	if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,}$/", $_REQUEST['id']) && sql_num_query("SELECT * FROM `User` WHERE `UID`=" . sql_escape($_REQUEST['id'])) > 0) {
		$id = $_REQUEST['id'];
		if (!isset ($_REQUEST['action'])) {
			$html .= "Hallo,<br />" .
			"hier kannst du den Eintrag &auml;ndern. Unter dem Punkt 'Gekommen' " .
			"wird der Engel als anwesend markiert, ein Ja bei Aktiv bedeutet, " .
			"dass der Engel aktiv war und damit ein Anspruch auf ein T-Shirt hat. " .
			"Wenn T-Shirt ein 'Ja' enth&auml;lt, bedeutet dies, dass der Engel " .
			"bereits sein T-Shirt erhalten hat.<br /><br />\n";

			$html .= "<form action=\"" . page_link_to("admin_user") . "&action=save&id=$id\" method=\"post\">\n";
			$html .= "<table border=\"0\">\n";
			$html .= "<input type=\"hidden\" name=\"Type\" value=\"Normal\">\n";

			$SQL = "SELECT * FROM `User` WHERE `UID`='" . $id . "'";
			$Erg = sql_query($SQL);

			$html .= "<tr><td>\n";
			$html .= "<table>\n";
			$html .= "  <tr><td>Nick</td><td>" .
			"<input type=\"text\" size=\"40\" name=\"eNick\" value=\"" .
			mysql_result($Erg, 0, "Nick") . "\"></td></tr>\n";
			$html .= "  <tr><td>lastLogIn</td><td>" .
			date("Y-m-d H:i", mysql_result($Erg, 0, "lastLogIn")) . "</td></tr>\n";
			$html .= "  <tr><td>Name</td><td>" .
			"<input type=\"text\" size=\"40\" name=\"eName\" value=\"" .
			mysql_result($Erg, 0, "Name") . "\"></td></tr>\n";
			$html .= "  <tr><td>Vorname</td><td>" .
			"<input type=\"text\" size=\"40\" name=\"eVorname\" value=\"" .
			mysql_result($Erg, 0, "Vorname") . "\"></td></tr>\n";
			$html .= "  <tr><td>Alter</td><td>" .
			"<input type=\"text\" size=\"5\" name=\"eAlter\" value=\"" .
			mysql_result($Erg, 0, "Alter") . "\"></td></tr>\n";
			$html .= "  <tr><td>Telefon</td><td>" .
			"<input type=\"text\" size=\"40\" name=\"eTelefon\" value=\"" .
			mysql_result($Erg, 0, "Telefon") . "\"></td></tr>\n";
			$html .= "  <tr><td>Handy</td><td>" .
			"<input type=\"text\" size=\"40\" name=\"eHandy\" value=\"" .
			mysql_result($Erg, 0, "Handy") . "\"></td></tr>\n";
			$html .= "  <tr><td>DECT</td><td>" .
			"<input type=\"text\" size=\"4\" name=\"eDECT\" value=\"" .
			mysql_result($Erg, 0, "DECT") . "\"></td></tr>\n";
			$html .= "  <tr><td>email</td><td>" .
			"<input type=\"text\" size=\"40\" name=\"eemail\" value=\"" .
			mysql_result($Erg, 0, "email") . "\"></td></tr>\n";
			$html .= "  <tr><td>ICQ</td><td>" .
			"<input type=\"text\" size=\"40\" name=\"eICQ\" value=\"" .
			mysql_result($Erg, 0, "ICQ") . "\"></td></tr>\n";
			$html .= "  <tr><td>jabber</td><td>" .
			"<input type=\"text\" size=\"40\" name=\"ejabber\" value=\"" .
			mysql_result($Erg, 0, "jabber") . "\"></td></tr>\n";
			$html .= "  <tr><td>Size</td><td>" .
			html_select_key('size', array (
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
			), mysql_result($Erg, 0, "Size")) . "</td></tr>\n";

			$options = array (
				'1' => "Yes",
				'0' => "No"
			);

			// Gekommen? 
			$html .= "  <tr><td>Gekommen</td><td>\n";
			$html .= html_options('eGekommen', $options, mysql_result($Erg, 0, "Gekommen")) . "</td></tr>\n";

			// Aktiv?
			$html .= "  <tr><td>Aktiv</td><td>\n";
			$html .= html_options('eAktiv', $options, mysql_result($Erg, 0, "Aktiv")) . "</td></tr>\n";

			// T-Shirt bekommen? 
			$html .= "  <tr><td>T-Shirt</td><td>\n";
			$html .= html_options('eTshirt', $options, mysql_result($Erg, 0, "Tshirt")) . "</td></tr>\n";

			$html .= "  <tr><td>Hometown</td><td>" .
			"<input type=\"text\" size=\"40\" name=\"Hometown\" value=\"" .
			mysql_result($Erg, 0, "Hometown") . "\"></td></tr>\n";

			$html .= "</table>\n</td><td valign=\"top\">" . displayavatar($id, false) . "</td></tr>";

			$html .= "</td></tr>\n";
			$html .= "</table>\n<br />\n";
			$html .= "<input type=\"hidden\" name=\"id\" value=\"" . $id . "\">\n";
			$html .= "<input type=\"submit\" value=\"Speichern\">\n";
			$html .= "</form>";

			$html .= "<hr />";

			$html .= "Hier kannst Du das Passwort dieses Engels neu setzen:<form action=\"" . page_link_to("admin_user") . "&action=change_pw&id=$id\" method=\"post\">\n";
			$html .= "<table>\n";
			$html .= "  <tr><td>Passwort</td><td>" .
			"<input type=\"password\" size=\"40\" name=\"new_pw\" value=\"\"></td></tr>\n";
			$html .= "  <tr><td>Wiederholung</td><td>" .
			"<input type=\"password\" size=\"40\" name=\"new_pw2\" value=\"\"></td></tr>\n";

			$html .= "</table>";
			$html .= "<input type=\"hidden\" name=\"id\" value=\"" . $id . "\">\n";
			$html .= "<input type=\"submit\" value=\"Speichern\">\n";
			$html .= "</form>";

			$html .= "<hr />";

			$html .= "<form action=\"" . page_link_to("admin_user") . "&action=delete&id=" . $id . "\" method=\"post\">\n";
			$html .= "<input type=\"hidden\" name=\"id\" value=\"" . $id . "\">\n";
			$html .= "<input type=\"submit\" value=\"Löschen\">\n";
			$html .= "</form>";

			$html .= "<hr />";
			$html .= funktion_db_element_list_2row("Freeloader Shifts", "SELECT `Remove_Time`, `Length`, `Comment` FROM `ShiftFreeloader` WHERE UID=" . $_REQUEST['id']);
		} else {
			switch ($_REQUEST['action']) {
				case 'delete' :
					if ($user['UID'] != $id) {
						sql_query("DELETE FROM `User` WHERE `UID`=" . sql_escape($id) . " LIMIT 1");
						sql_query("DELETE FROM `UserGroups` WHERE `uid`=" . sql_escape($id));
						sql_query("UPDATE `ShiftEntry` SET `UID`=0, `Comment`=NULL WHERE `UID`=" . sql_escape($id));
						$html .= success("Benutzer gelöscht!");
					} else {
						$html .= error("Du kannst Dich nicht selber löschen!");
					}
					break;

				case 'save' :
					$SQL = "UPDATE `User` SET ";
					$SQL .= " `Nick` = '" . $_POST["eNick"] . "', `Name` = '" . $_POST["eName"] . "', " .
					"`Vorname` = '" . $_POST["eVorname"] . "', " .
					"`Telefon` = '" . $_POST["eTelefon"] . "', " .
					"`Handy` = '" . $_POST["eHandy"] . "', " .
					"`Alter` = '" . $_POST["eAlter"] . "', " .
					"`DECT` = '" . $_POST["eDECT"] . "', " .
					"`email` = '" . $_POST["eemail"] . "', " .
					"`ICQ` = '" . $_POST["eICQ"] . "', " .
					"`jabber` = '" . $_POST["ejabber"] . "', " .
					"`Size` = '" . $_POST["eSize"] . "', " .
					"`Gekommen`= '" . $_POST["eGekommen"] . "', " .
					"`Aktiv`= '" . $_POST["eAktiv"] . "', " .
					"`Tshirt` = '" . $_POST["eTshirt"] . "', " .
					"`Hometown` = '" . $_POST["Hometown"] . "' " .
					"WHERE `UID` = '" . $id .
					"' LIMIT 1;";
					sql_query($SQL);
					$html .= success("Änderung wurde gespeichert...\n");
					break;

				case 'change_pw' :
					if ($_REQUEST['new_pw'] != "" && $_REQUEST['new_pw'] == $_REQUEST['new_pw2']) {
						sql_query("UPDATE `User` SET `Passwort`='" . sql_escape(PassCrypt($_REQUEST['new_pw'])) . "' WHERE `UID`=" . sql_escape($id) . " LIMIT 1");
						$html .= success("Passwort neu gesetzt.");
					} else {
						$html .= error("Die Eingaben müssen übereinstimmen und dürfen nicht leer sein!");
					}
					break;
			}
		}
	} else {
		// Userliste, keine UID uebergeben...

		$html .= "<a href=\"" . page_link_to("register") . "\">Neuen Engel eintragen &raquo;</a><br /><br />\n";

		if (!isset ($_GET["OrderBy"]))
			$_GET["OrderBy"] = "Nick";
		$SQL = "SELECT * FROM `User` ORDER BY `" . sql_escape($_GET["OrderBy"]) . "` ASC";
		$Erg = sql_query($SQL);

		// anzahl zeilen
		$Zeilen = mysql_num_rows($Erg);

		$html .= "Anzahl Engel: $Zeilen<br /><br />\n";
		$html .= '
									<table width="100%" class="border" cellpadding="2" cellspacing="1"> <thead>
									  <tr class="contenttopic">
									    <th>
									      <a href="' . page_link_to("admin_user") . '&OrderBy=Nick">Nick</a>
									    </th>
									    <th><a href="' . page_link_to("admin_user") . '&OrderBy=Vorname">Vorname</a> <a href="' . page_link_to("admin_user") . '&OrderBy=Name">Name</a></th>
									    <th><a href="' . page_link_to("admin_user") . '&OrderBy=Alter">Alter</a></th>
									    <th>
									      <a href="' . page_link_to("admin_user") . '&OrderBy=email">E-Mail</a>
									    </th>
									    <th><a href="' . page_link_to("admin_user") . '&OrderBy=Size">Gr&ouml;&szlig;e</a></th>
									    <th><a href="' . page_link_to("admin_user") . '&OrderBy=Gekommen">Gekommen</a></th>
									    <th><a href="' . page_link_to("admin_user") . '&OrderBy=Aktiv">Aktiv</a></th>
									    <th><a href="' . page_link_to("admin_user") . '&OrderBy=Tshirt">T-Shirt</a></th>
									    <th><a href="' . page_link_to("admin_user") . '&OrderBy=CreateDate">Registrier</a></th>
									    <th>&Auml;nd.</th>
									  </tr></thead>';
		$Gekommen = 0;
		$Active = 0;
		$Tshirt = 0;

		for ($n = 0; $n < $Zeilen; $n++) {
			$title = "";
			$user_groups = sql_select("SELECT * FROM `UserGroups` JOIN `Groups` ON (`Groups`.`UID` = `UserGroups`.`group_id`) WHERE `UserGroups`.`uid`=" . sql_escape(mysql_result($Erg, $n, "UID")) . " ORDER BY `Groups`.`Name`");
			$groups = array ();
			foreach ($user_groups as $user_group) {
				$groups[] = $user_group['Name'];
			}
			$title .= 'Groups: ' . join(", ", $groups) . "<br />";
			if (strlen(mysql_result($Erg, $n, "Telefon")) > 0)
				$title .= "Tel: " . mysql_result($Erg, $n, "Telefon") . "<br />";
			if (strlen(mysql_result($Erg, $n, "Handy")) > 0)
				$title .= "Handy: " . mysql_result($Erg, $n, "Handy") . "<br />";
			if (strlen(mysql_result($Erg, $n, "DECT")) > 0)
				$title .= "DECT: <a href=\"./dect.php?custum=" . mysql_result($Erg, $n, "DECT") . "\">" .
				mysql_result($Erg, $n, "DECT") . "</a><br />";
			if (strlen(mysql_result($Erg, $n, "Hometown")) > 0)
				$title .= "Hometown: " . mysql_result($Erg, $n, "Hometown") . "<br />";
			if (strlen(mysql_result($Erg, $n, "lastLogIn")) > 0)
				$title .= "Last login: " . date("Y-m-d H:i", mysql_result($Erg, $n, "lastLogIn")) . "<br />";
			if (strlen(mysql_result($Erg, $n, "Art")) > 0)
				$title .= "Type: " . mysql_result($Erg, $n, "Art") . "<br />";
			if (strlen(mysql_result($Erg, $n, "ICQ")) > 0)
				$title .= "ICQ: " . mysql_result($Erg, $n, "ICQ") . "<br />";
			if (strlen(mysql_result($Erg, $n, "jabber")) > 0)
				$title .= "jabber: " . mysql_result($Erg, $n, "jabber") . "<br />";

			$html .= "<tr class=\"content\">\n";
			$html .= "\t<td>" . mysql_result($Erg, $n, "Nick") . "</td>\n";
			$html .= "\t<td>" . mysql_result($Erg, $n, "Vorname") . " " . mysql_result($Erg, $n, "Name") . "</td>\n";
			$html .= "\t<td>" . mysql_result($Erg, $n, "Alter") . "</td>\n";
			$html .= "\t<td>";
			if (strlen(mysql_result($Erg, $n, "email")) > 0)
				$html .= "<a href=\"mailto:" . mysql_result($Erg, $n, "email") . "\">" .
				mysql_result($Erg, $n, "email") . "</a>";
			$html .= '<div class="hidden">' . $title . '</div>';
			$html .= "</td>\n";
			$html .= "\t<td>" . mysql_result($Erg, $n, "Size") . "</td>\n";
			$Gekommen += mysql_result($Erg, $n, "Gekommen");
			$html .= "\t<td>" . mysql_result($Erg, $n, "Gekommen") . "</td>\n";
			$Active += mysql_result($Erg, $n, "Aktiv");
			$html .= "\t<td>" . mysql_result($Erg, $n, "Aktiv") . "</td>\n";
			$Tshirt += mysql_result($Erg, $n, "Tshirt");
			$html .= "\t<td>" . mysql_result($Erg, $n, "Tshirt") . "</td>\n";
			$html .= "<td>" . mysql_result($Erg, $n, "CreateDate") . "</td>";
			$html .= "\t<td>" . '<a href="' . page_link_to("admin_user") . '&id=' . mysql_result($Erg, $n, "UID") . '">Edit</a>' .
			"</td>\n";
			$html .= "</tr>\n";
		}
		$html .= "<tr>" .
		"<td></td><td></td><td></td><td></td><td></td>" .
		"<td>$Gekommen</td><td>$Active</td><td>$Tshirt</td><td></td><td></td></tr>\n";
		$html .= "\t</table>\n";
		// Ende Userliste

		$html .= "<hr /><h2>Statistics</h2>";
		$html .= funktion_db_element_list_2row("Hometown", "SELECT COUNT(`Hometown`), `Hometown` FROM `User` GROUP BY `Hometown`");

		$html .= "<br />\n";

		$html .= funktion_db_element_list_2row("Engeltypen", "SELECT COUNT(`Art`), `Art` FROM `User` GROUP BY `Art`");

		$html .= "<br />\n";

		$html .= funktion_db_element_list_2row("Used Groups", "SELECT Groups.Name AS 'GroupName', COUNT(Groups.Name) AS Count FROM `UserGroups` " .
		"LEFT JOIN `Groups` ON Groups.UID = UserGroups.group_id " .
		"WHERE (UserGroups.group_id!='NULL') " .
		"GROUP BY `GroupName` " .
		"");
	}
	return $html;
}
?>