<?php
function admin_language() {
	global $user;

	$html = "";
	if (!isset ($_POST["TextID"])) {
		$html .= Get_Text("Hello") . $user['Nick'] . ", <br />\n";
		$html .= Get_Text("pub_sprache_text1") . "<br /><br />\n";

		$html .= "<a href=\"" . page_link_to("admin_language") . "&ShowEntry=y\">" . Get_Text("pub_sprache_ShowEntry") . "</a>";
		// ausgabe Tabellenueberschift
		$SQL_Sprachen = "SELECT `Sprache` FROM `Sprache` GROUP BY `Sprache`;";
		$erg_Sprachen = sql_query($SQL_Sprachen);

		for ($i = 0; $i < mysql_num_rows($erg_Sprachen); $i++)
			$Sprachen[mysql_result($erg_Sprachen, $i, "Sprache")] = $i;

		$html .= "\t<table border=\"0\" class=\"border\" cellpadding=\"2\" cellspacing=\"1\">\n\t\t<tr>";
		$html .= "\t\t<td class=\"contenttopic\"><b>" . Get_Text("pub_sprache_TextID") . "</b></td>";
		foreach ($Sprachen as $Name => $Value)
			$html .= "<td class=\"contenttopic\"><b>" .
			Get_Text("pub_sprache_Sprache") . " " . $Name .
			"</b></td>";
		$html .= "\t\t<td class=\"contenttopic\"><b>" . Get_Text("pub_sprache_Edit") . "</b></td>";
		$html .= "\t\t</tr>";

		if (isset ($_GET["ShowEntry"])) {
			// ausgabe eintraege
			$SQL = "SELECT * FROM `Sprache` ORDER BY `TextID`;";
			$erg = sql_query($SQL);

			$TextID_Old = mysql_result($erg, 0, "TextID");
			for ($i = 0; $i < mysql_num_rows($erg); $i++) {
				$TextID_New = mysql_result($erg, $i, "TextID");
				if ($TextID_Old != $TextID_New) {
					$html .= "<form action=\"" . page_link_to("admin_language") . "\" method=\"post\">";
					$html .= "<tr class=\"content\">\n";
					$html .= "\t\t<td>$TextID_Old " .
					"<input name=\"TextID\" type=\"hidden\" value=\"$TextID_Old\"> </td>\n";

					foreach ($Sprachen as $Name => $Value) {
						$Value = html_entity_decode($Value, ENT_QUOTES);
						$html .= "\t\t<td><textarea name=\"$Name\" cols=\"22\" rows=\"8\">$Value</textarea></td>\n";
						$Sprachen[$Name] = "";
					}

					$html .= "\t\t<td><input type=\"submit\" value=\"Save\"></td>\n";
					$html .= "</tr>";
					$html .= "</form>\n";
					$TextID_Old = $TextID_New;
				}
				$Sprachen[mysql_result($erg, $i, "Sprache")] = mysql_result($erg, $i, "Text");
			} /*FOR*/
		}

		//fuer neu eintraege
		$html .= "<form action=\"" . page_link_to("admin_language") . "\" method=\"post\">";
		$html .= "<tr class=\"content\">\n";
		$html .= "\t\t<td><input name=\"TextID\" type=\"text\" size=\"40\" value=\"new\"> </td>\n";

		foreach ($Sprachen as $Name => $Value)
			$html .= "\t\t<td><textarea name=\"$Name\" cols=\"22\" rows=\"8\">$Name Text</textarea></td>\n";

		$html .= "\t\t<td><input type=\"submit\" value=\"Save\"></td>\n";
		$html .= "</tr>";
		$html .= "</form>\n";

		$html .= "</table>\n";
	} /*if( !isset( $TextID )  )*/
	else {
		$html .= "edit: " . $_POST["TextID"] . "<br /><br />";
		foreach ($_POST as $k => $v) {
			if ($k != "TextID") {
				$sql_test = "SELECT * FROM `Sprache` " .
				"WHERE `TextID`='" . $_POST["TextID"] . "' AND `Sprache`='$k'";
				$erg_test = sql_query($sql_test);

				if (mysql_num_rows($erg_test) == 0) {
					$sql_save = "INSERT INTO `Sprache` (`TextID`, `Sprache`, `Text`) " .
					"VALUES ('" . $_POST["TextID"] . "', '$k', '$v')";
					$html .= $sql_save . "<br />";
					$Erg = sql_query($sql_save);
					$html .= success("$k Save: OK<br />\n");
				} else
					if (mysql_result($erg_test, 0, "Text") != $v) {
						$sql_save = "UPDATE `Sprache` SET `Text`='$v' " .
						"WHERE `TextID`='" . $_POST["TextID"] . "' AND `Sprache`='$k' ";
						$html .= $sql_save . "<br />";
						$Erg = sql_query($sql_save);
						$html .= success(" $k Update: OK<br />\n");
					} else
						$html .= "\t $k no changes<br />\n";
			}
		}

	}
	return $html;
}
?>

