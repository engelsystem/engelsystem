<?php
function user_wakeup() {
	global $user;

	$html = "";

	if (isset ($_REQUEST['action'])) {
		switch ($_REQUEST['action']) {
			case 'create' :
				$date = DateTime::createFromFormat("Y-m-d H:i", $_REQUEST['Date']);
				if ($date != null) {
					$date = $date->getTimestamp();
					$bemerkung = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}\n]{1,})/ui", '', strip_tags($_REQUEST['Bemerkung']));
					$ort = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui", '', strip_tags($_REQUEST['Ort']));
					$SQL = "INSERT INTO `Wecken` (`UID`, `Date`, `Ort`, `Bemerkung`) " .
					"VALUES ('" . $user['UID'] . "', '" . $date . "', '" . $ort . "', " .
					"'" . $bemerkung . "')";
					sql_query($SQL);
					$html .= success(Get_Text(4));
				} else
					$html .= error("Broken date!");
				break;

			case 'delete' :
				if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
					$id = $_REQUEST['id'];
				else
					return error("Incomplete call, missing wake-up ID.");

				$wakeup = sql_select("SELECT * FROM `Wecken` WHERE `ID`=" . sql_escape($id) . " LIMIT 1");
				if (count($wakeup) > 0 && $wakeup[0]['UID'] == $user['UID']) {
					sql_query("DELETE FROM `Wecken` WHERE `ID`=" . sql_escape($id) . " LIMIT 1");
					$html .= success("Wake-up call deleted.");
				} else
					return error("No wake-up found.");
				break;
		}
	}

	$html .= "<p>" . Get_Text("Hello") . $user['Nick'] . ",<br />" . Get_Text("pub_wake_beschreibung") . "</p>\n\n";
	$html .= Get_Text("pub_wake_beschreibung2");
	$html .= '
<table border="0" width="100%" class="border" cellpadding="2" cellspacing="1">
  <tr class="contenttopic">
    <th>' . Get_Text("pub_wake_Datum") . '</th>
    <th>' . Get_Text("pub_waeckliste_Nick") . '</th>
    <th>' . Get_Text("pub_wake_Ort") . '</th>
    <th>' . Get_Text("pub_wake_Bemerkung") . '</th>
    <th></th>
        </tr>
';

	$sql = "SELECT * FROM `Wecken` ORDER BY `Date` ASC";
	$Erg = sql_query($sql);
	$count = mysql_num_rows($Erg);

	for ($i = 0; $i < $count; $i++) {
		$row = mysql_fetch_row($Erg);
		$html .= '<tr class="content">';
		$html .= '<td>' . date("Y-m-d H:i", mysql_result($Erg, $i, "Date")) . ' </td>';
		$html .= '<td>' . UID2Nick(mysql_result($Erg, $i, "UID")) . ' </td>';
		$html .= '<td>' . mysql_result($Erg, $i, "Ort") . ' </td>';
		$html .= '<td>' . mysql_result($Erg, $i, "Bemerkung") . ' </td>';
		if (mysql_result($Erg, $i, "UID") == $user['UID'])
			$html .= '<td><a href="' . page_link_to("user_wakeup") . '&action=delete&id=' . mysql_result($Erg, $i, "ID") . "\">" . Get_Text("pub_wake_del") . '</a></td>';
		else
			$html .= '<td></td>';
		$html .= '</tr>';
	}

	$html .= '</table><hr />' . Get_Text("pub_wake_Text2") . '
<form action="' . page_link_to("user_wakeup") . '&action=create" method="post">
<table>
 <tr>
   <td align="right">' . Get_Text("pub_wake_Datum") . ':</td>
   <td><input type="text" name="Date" value="' . date("Y-m-d H:i") . '"></td>
 </tr>
 <tr>
   <td align="right">' . Get_Text("pub_wake_Ort") . '</td>
  <td><input type="text" name="Ort" value="Tent 23"></td>
 </tr>
 <tr>
   <td align="right">' . Get_Text("pub_wake_Bemerkung") . '</td>
  <td><textarea name="Bemerkung" rows="5" cols="40">knock knock leo, follow the white rabbit to the blue tent</textarea></td>
 </tr>
</table>
<input type="submit" name="submit" value="' . Get_Text("pub_wake_bouton") . '" />
</form>';

	return $html;
}
?>