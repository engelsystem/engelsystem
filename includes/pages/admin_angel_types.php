<?php
function admin_angel_types() {
	include ("includes/funktion_db.php");

	$html = "";
	if (!isset ($_REQUEST['action'])) {
		$table = "";
		$angel_types = sql_select("SELECT * FROM `AngelTypes` ORDER BY `Name`");
		foreach ($angel_types as $angel_type)
			$table .= '<tr><td>' . $angel_type['Name'] . '</td><td>' . $angel_type['Man'] . '</td><td><a href="' . page_link_to("admin_angel_types") . '&action=edit&id=' . $angel_type['TID'] . '">Edit</a></td></tr>';

		$html .= template_render('../templates/admin_angel_types.html', array (
			'link' => page_link_to("admin_angel_types"),
			'table' => $table
		));
	} else {
		switch ($_REQUEST['action']) {
			case 'create' :
				$name = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui", '', strip_tags($_REQUEST['name']));
				$man = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui", '', strip_tags($_REQUEST['man']));
				sql_query("INSERT INTO `AngelTypes` SET `Name`='" . sql_escape($name) . "', `Man`='" . sql_escape($man) . "'");
				header("Location: " . page_link_to("admin_angel_types"));
				break;

			case 'edit' :
				if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
					$id = $_REQUEST['id'];
				else
					return error("Incomplete call, missing AngelType ID.");

				$angel_type = sql_select("SELECT * FROM `AngelTypes` WHERE `TID`=" . sql_escape($id) . " LIMIT 1");
				if (count($angel_type) > 0) {
					list ($angel_type) = $angel_type;

					$html .= template_render('../templates/admin_angel_types_edit_form.html', array (
						'link' => page_link_to("admin_angel_types"),
						'id' => $id,
						'name' => $angel_type['Name'],
						'man' => $angel_type['Man']
					));
				} else
					return error("No Angel Type found.");
				break;

			case 'save' :
				if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
					$id = $_REQUEST['id'];
				else
					return error("Incomplete call, missing AngelType ID.");

				$angel_type = sql_select("SELECT * FROM `AngelTypes` WHERE `TID`=" . sql_escape($id) . " LIMIT 1");
				if (count($angel_type) > 0) {
					list ($angel_type) = $angel_type;

					$name = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui", '', strip_tags($_REQUEST['name']));
					$man = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui", '', strip_tags($_REQUEST['man']));
					sql_query("UPDATE `AngelTypes` SET `Name`='" . sql_escape($name) . "', `Man`='" . sql_escape($man) . "' WHERE `TID`=" . sql_escape($id) . " LIMIT 1");
					header("Location: " . page_link_to("admin_angel_types"));
				} else
					return error("No Angel Type found.");
				break;

			case 'delete' :
				if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
					$id = $_REQUEST['id'];
				else
					return error("Incomplete call, missing AngelType ID.");

				$angel_type = sql_select("SELECT * FROM `AngelTypes` WHERE `TID`=" . sql_escape($id) . " LIMIT 1");
				if (count($angel_type) > 0) {
					sql_query("DELETE FROM `AngelTypes` WHERE `TID`=" . sql_escape($id) . " LIMIT 1");
					sql_query("DELETE FROM `RoomAngelTypes` WHERE `angel_type_id`=" . sql_escape($id) . " LIMIT 1");
					header("Location: " . page_link_to("admin_angel_types"));
				} else
					return error("No Angel Type found.");
				break;
		}
	}

	return $html;
	$Sql = "SELECT * FROM `EngelType` ORDER BY `NAME`";
	$Erg = mysql_query($Sql, $con);

	if (!IsSet ($_GET["action"])) {
		echo "Hallo " . $_SESSION['Nick'] .
		",<br />\nhier hast du die M&ouml;glichkeit, neue Engeltypen f&uuml;r die Schichtpl&auml;ne einzutragen " .
		"oder vorhandene abzu&auml;ndern:<br /><br />\n";

		echo "<a href=\"./EngelType.php?action=new\">- Neuen EngelType eintragen</a><br />\n";

		echo "<table width=\"100%\" class=\"border\" cellpadding=\"2\" cellspacing=\"1\">\n";
		echo "<tr class=\"contenttopic\">\n";

		for ($i = 1; $i < mysql_num_fields($Erg); $i++) {
			echo "\t<td>" . mysql_field_name($Erg, $i) . "</td>";
		}
		echo "\t<td>&Auml;ndern</td>";
		echo "</tr>";

		for ($t = 0; $t < mysql_num_rows($Erg); $t++) {
			echo "\t<tr class=\"content\">\n";
			for ($j = 1; $j < mysql_num_fields($Erg); $j++) {
				echo "\t\t<td>" . mysql_result($Erg, $t, $j) . "</td>\n";
			}
			echo "\t\t<td><a href=\"./EngelType.php?action=change&TID=" . mysql_result($Erg, $t, "TID") . "\">###</a></td>\n";
			echo "\t</tr>\n";
		} // ende Auflistung Raeume
		echo "</table>";
	} else {

		switch ($_GET["action"]) {

			case 'new' :
				echo "Neuen EngelType einrichten: <br />";
				echo "<form action=\"./EngelType.php\" method=\"GET\">\n";
				echo "<table>\n";

				for ($Uj = 1; $Uj < mysql_num_fields($Erg); $Uj++) {
					echo "<td>" . mysql_field_name($Erg, $Uj) . "</td>" .
					"<td><input type=\"text\" size=\"40\" name=\"" . mysql_field_name($Erg, $Uj) . "\"></td></tr>\n";
				}
				echo "</table>\n";
				echo "<input type=\"hidden\" name=\"action\" value=\"newsave\">\n";
				echo "<input type=\"submit\" value=\"sichern...\">\n";
				echo "</form>";
				break;

			case 'newsave' :
				$vars = $_GET;
				$count = count($vars) - 1;
				$vars = array_splice($vars, 0, $count);
				$Keys = "";
				$Values = "";
				foreach ($vars as $key => $value) {
					$Keys .= ", `$key`";
					$Values .= ", '$value'";
				}

				if (runSQL_log("INSERT INTO `EngelType` (" . substr($Keys, 2) . ") VALUES (" . substr($Values, 2) . ")", "save new EngelType")) {
					SetHeaderGo2Back();

					$SQL2 = "SELECT * FROM `EngelType` WHERE `Name`='" . $_GET["Name"] . "'";
					$ERG = mysql_query($SQL2, $con);

					if (mysql_num_rows($ERG) == 1)
						runSQL_log("ALTER TABLE `Room` ADD `DEFAULT_EID_" . mysql_result($ERG, 0, 0) .
						"` INT DEFAULT '0' NOT NULL;", "add new EngelType in Romm Table");
				}
				break;

			case 'change' :
				if (!IsSet ($_GET["TID"]))
					echo "Fehlerhafter Aufruf!";
				else {
					echo "Raum ab&auml;ndern:\n";
					echo "Hier kannst du eintragen, den EngelType &auml;ndern.";
					echo "<form action=\"./EngelType.php\" method=\"GET\">\n";
					echo "<table>\n";

					$SQL2 = "SELECT * FROM `EngelType` WHERE `TID`='" . $_GET["TID"] . "'";
					$ERG = mysql_query($SQL2, $con);

					for ($Uj = 1; $Uj < mysql_num_fields($ERG); $Uj++)
						echo "<tr><td>" . mysql_field_name($ERG, $Uj) . "</td>" .
						"<td><input type=\"text\" size=\"40\" name=\"e" . mysql_field_name($ERG, $Uj) . "\" " .
						"value=\"" . mysql_result($ERG, 0, $Uj) . "\"></td></tr>\n";

					echo "</table>\n";
					echo "<input type=\"hidden\" name=\"eTID\" value=\"" . $_GET["TID"] . "\">\n";
					echo "<input type=\"hidden\" name=\"action\" value=\"changesave\">\n";
					echo "<input type=\"submit\" value=\"sichern...\">\n";
					echo "</form>";
					echo "<form action=\"./EngelType.php\" method=\"GET\">\n";
					echo "<input type=\"hidden\" name=\"TID\" value=\"" . $_GET["TID"] . "\">\n";
					echo "<input type=\"hidden\" name=\"action\" value=\"delete\">\n";
					echo "<input type=\"submit\" value=\"L&ouml;schen...\">";
					echo "</form>";
				}
				break;

			case 'changesave' :
				$vars = $_GET;
				$count = count($vars) - 2;
				$vars = array_splice($vars, 0, $count);
				$keys = "";
				$sql = "";
				foreach ($vars as $key => $value) {
					$keys = substr($key, 1);
					$sql .= ", `" . $keys . "`='" . $value . "'";
				}
				runSQL_log("UPDATE `EngelType` SET " . substr($sql, 2) . " WHERE `TID`='" . $_GET["eTID"] . "'", "Save Change EngelType");
				SetHeaderGo2Back();
				break;

			case 'delete' :
				if (IsSet ($_GET["TID"])) {
					if (runSQL_log("DELETE FROM `EngelType` WHERE `TID`='" . $_GET["TID"] . "'", "delete EngelType"))
						runSQL_log("ALTER TABLE `Room` DROP `DEFAULT_EID_" . $_GET["TID"] . "`;", "delete EngelType in Room Table");
				} else
					echo "Fehlerhafter Aufruf";
				SetHeaderGo2Back();
				break;
		}
	}

	include ("includes/footer.php");
}
?>
