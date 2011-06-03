<?php
function user_unread_messages() {
	global $user, $privileges;

	if (in_array("user_messages", $privileges)) {
		$new_messages = sql_num_query("SELECT * FROM `Messages` WHERE isRead='N' AND `RUID`=" . sql_escape($user['UID']));

		if ($new_messages > 0)
			return sprintf(
				'<p class="notice"><a href="%s">%s %s %s</a></p><hr />',
				page_link_to("user_messages"),
				Get_Text("pub_messages_new1"),
				$new_messages,
				Get_Text("pub_messages_new2")
			);
	}

	return "";
}

function user_messages() {
	global $user;

	if (!isset ($_REQUEST['action'])) {
		$users = sql_select("SELECT * FROM `User` WHERE NOT `UID`="
			. sql_escape($user['UID']) . " ORDER BY `Nick`");

		$to_select_data = array (
			"" => "Select receiver..."
		);

		foreach ($users as $u)
			$to_select_data[$u['UID']] = $u['Nick'];

		$to_select = html_select_key('to', $to_select_data, '');

		$messages_html = "";
		$messages = sql_select("SELECT * FROM `Messages` WHERE `SUID`="
			. sql_escape($user['UID'])
			. " OR `RUID`=" . sql_escape($user['UID'])
			. " ORDER BY `isRead`,`Datum` DESC"
		);
		foreach ($messages as $message) {

			$messages_html .= sprintf(
				'<tr %s> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td>'
				.'<td>%s</td>',
				($message['isRead'] == 'N' ? ' class="new_message"' : ''),
				($message['isRead'] == 'N' ? '•' : ''),
				date("Y-m-d H:i", $message['Datum']),
				UID2Nick($message['SUID']),
				UID2Nick($message['RUID']),
				str_replace("\n", '<br />', $message['Text'])
			);

			$messages_html .= '<td>';
			if ($message['RUID'] == $user['UID']) {
				if ($message['isRead'] == 'N')
					$messages_html .= '<a href="' . page_link_to("user_messages") . '&action=read&id=' . $message['id'] . '">' . Get_Text("pub_messages_MarkRead") . '</a>';
			} else {
				$messages_html .= '<a href="' . page_link_to("user_messages") . '&action=delete&id=' . $message['id'] . '">' . Get_Text("pub_messages_DelMsg") . '</a>';
			}
			$messages_html .= '</td></tr>';
		}

		return template_render('../templates/user_messages.html', array (
			'link' => page_link_to("user_messages"),
			'greeting' => Get_Text("Hello") . $user['Nick'] . ", <br />\n"
			            . Get_Text("pub_messages_text1") . "<br /><br />\n",
			'messages' => $messages_html,
			'new_label' => Get_Text("pub_messages_Neu"),
			'date_label' => Get_Text("pub_messages_Datum"),
			'from_label' => Get_Text("pub_messages_Von"),
			'to_label' => Get_Text("pub_messages_An"),
			'text_label' => Get_Text("pub_messages_Text"),
			'date' => date("Y-m-d H:i"),
			'from' => $user['Nick'],
			'to_select' => $to_select,
			'submit_label' => Get_Text("save")
		));
	} else {
		switch ($_REQUEST['action']) {
			case "read" :
				if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
					$id = $_REQUEST['id'];
				else
					return error("Incomplete call, missing Message ID.");

				$message = sql_select("SELECT * FROM `Messages` WHERE `id`=" . sql_escape($id) . " LIMIT 1");
				if (count($message) > 0 && $message[0]['RUID'] == $user['UID']) {
					sql_query("UPDATE `Messages` SET `isRead`='Y' WHERE `id`=" . sql_escape($id) . " LIMIT 1");
					header("Location: " . page_link_to("user_messages"));
				} else
					return error("No Message found.");
				break;

			case "delete" :
				if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
					$id = $_REQUEST['id'];
				else
					return error("Incomplete call, missing Message ID.");

				$message = sql_select("SELECT * FROM `Messages` WHERE `id`=" . sql_escape($id) . " LIMIT 1");
				if (count($message) > 0 && $message[0]['SUID'] == $user['UID']) {
					sql_query("DELETE FROM `Messages` WHERE `id`=" . sql_escape($id) . " LIMIT 1");
					header("Location: " . page_link_to("user_messages"));
				} else
					return error("No Message found.");
				break;

			case "send" :
				$text = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}\n]{1,})/ui", '', strip_tags($_REQUEST['text']));
				$to = preg_replace("/([^0-9]{1,})/ui", '', strip_tags($_REQUEST['to']));
				if ($text != "" && is_numeric($to) && sql_num_query("SELECT * FROM `User` WHERE `UID`=" . sql_escape($to) . " AND NOT `UID`=" . sql_escape($user['UID']) . " LIMIT 1") > 0) {
					sql_query("INSERT INTO `Messages` SET `Datum`=" . sql_escape(time()) . ", `SUID`=" . sql_escape($user['UID']) . ", `RUID`=" . sql_escape($to) . ", `Text`='" . sql_escape($text) . "'");
					header("Location: " . page_link_to("user_messages"));
				} else {
					return error(Get_Text("pub_messages_Send_Error"));
				}
				break;
		}
		return "";
	}

	if (!isset ($_GET["action"]))
		$_GET["action"] = "start";

	switch ($_GET["action"]) {
		case "start" :
			echo Get_Text("Hello") . $_SESSION['Nick'] . ", <br />\n";
			echo Get_Text("pub_messages_text1") . "<br /><br />\n";

			//show exist Messages
			$SQL = "SELECT * FROM `Messages` WHERE `SUID`='" . $_SESSION["UID"] . "' OR `RUID`='" . $_SESSION["UID"] . "'";
			$erg = mysql_query($SQL, $con);

			echo "<table border=\"0\" class=\"border\" cellpadding=\"2\" cellspacing=\"1\">\n";
			echo "<tr>\n";
			echo "<td class=\"contenttopic\"><b>" . Get_Text("pub_messages_Datum") . "</b></td>\n";
			echo "<td class=\"contenttopic\"><b>" . Get_Text("pub_messages_Von") . "</b></td>\n";
			echo "<td class=\"contenttopic\"><b>" . Get_Text("pub_messages_An") . "</b></td>\n";
			echo "<td class=\"contenttopic\"><b>" . Get_Text("pub_messages_Text") . "</b></td>\n";
			echo "<td class=\"contenttopic\"></td>\n";
			echo "</tr>\n";

			for ($i = 0; $i < mysql_num_rows($erg); $i++) {
				echo "<tr class=\"content\">\n";
				echo "<td>" . mysql_result($erg, $i, "Datum") . "</td>\n";
				echo "<td>" . UID2Nick(mysql_result($erg, $i, "SUID")) . "</td>\n";
				echo "<td>" . UID2Nick(mysql_result($erg, $i, "RUID")) . "</td>\n";
				echo "<td>" . mysql_result($erg, $i, "Text") . "</td>\n";
				echo "<td>";

				if (mysql_result($erg, $i, "RUID") == $_SESSION["UID"]) {
					echo "<a href=\"?action=DelMsg&Datum=" . mysql_result($erg, $i, "Datum") . "\">" . Get_Text("pub_messages_DelMsg") . "</a>";

					if (mysql_result($erg, $i, "isRead") == "N")
						echo "<a href=\"?action=MarkRead&Datum=" . mysql_result($erg, $i, "Datum") . "\">" . Get_Text("pub_messages_MarkRead") . "</a>";
				} else {
					if (mysql_result($erg, $i, "isRead") == "N")
						echo Get_Text("pub_messages_NotRead");
				}

				echo "</td>\n";
				echo "</tr>\n";
			}

			// send Messeges
			echo "<form action=\"" . $_SERVER['SCRIPT_NAME'] . "?action=SendMsg\" method=\"POST\">";
			echo "<tr class=\"content\">\n";
			echo "<td></td>\n";
			echo "<td></td>\n";

			// Listet alle Nicks auf
			echo "<td><select name=\"RUID\">\n";

			$usql = "SELECT * FROM `User` WHERE (`UID`!='" . $_SESSION["UID"] . "') ORDER BY `Nick`";
			$uErg = mysql_query($usql, $con);
			$urowcount = mysql_num_rows($uErg);

			for ($k = 0; $k < $urowcount; $k++) {
				echo "<option value=\"" . mysql_result($uErg, $k, "UID") . "\">" . mysql_result($uErg, $k, "Nick") . "</option>\n";
			}

			echo "</select></td>\n";
			echo "<td><textarea name=\"Text\"  cols=\"30\" rows=\"10\"></textarea></td>\n";
			echo "<td><input type=\"submit\" value=\"" . Get_Text("save") . "\"></td>\n";
			echo "</tr>\n";
			echo "</form>";

			echo "</table>\n";
			break;

		case "SendMsg" :
			echo Get_Text("pub_messages_Send1") . "...<br />\n";

			$SQL = "INSERT INTO `Messages` ( `Datum` , `SUID` , `RUID` , `Text` ) VALUES (" .
			"'" . gmdate("Y-m-j H:i:s", time()) . "', " .
			"'" . $_SESSION["UID"] . "', " .
			"'" . $_POST["RUID"] . "', " .
			"'" . $_POST["Text"] . "');";

			$Erg = mysql_query($SQL, $con);

			if ($Erg == 1)
				echo Get_Text("pub_messages_Send_OK") . "\n";
			else
				echo Get_Text("pub_messages_Send_Error") . "...\n(" . mysql_error($con) . ")";
			break;

		case "MarkRead" :
			$SQL = "UPDATE `Messages` SET `isRead` = 'Y' " .
			"WHERE `Datum` = '" . $_GET["Datum"] . "' AND `RUID`='" . $_SESSION["UID"] . "' " .
			"LIMIT 1 ;";
			$Erg = mysql_query($SQL, $con);

			if ($Erg == 1)
				echo Get_Text("pub_messages_MarkRead_OK") . "\n";
			else
				echo Get_Text("pub_messages_MarkRead_KO") . "...\n(" . mysql_error($con) . ")";
			break;

		case "DelMsg" :
			$SQL = "DELETE FROM `Messages` " .
			"WHERE `Datum` = '" . $_GET["Datum"] . "' AND `RUID` ='" . $_SESSION["UID"] . "' " .
			"LIMIT 1;";
			$Erg = mysql_query($SQL, $con);

			if ($Erg == 1)
				echo Get_Text("pub_messages_DelMsg_OK") . "\n";
			else
				echo Get_Text("pub_messages_DelMsg_KO") . "...\n(" . mysql_error($con) . ")";
			break;

		default :
			echo Get_Text("pub_messages_NoCommand");
	}
}
?>
