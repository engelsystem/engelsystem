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
					$bemerkung = strip_request_item_nl('Bemerkung');
					$ort = strip_request_item('Ort');
					$SQL = "INSERT INTO `Wecken` (`UID`, `Date`, `Ort`, `Bemerkung`) "
					. "VALUES ('" . sql_escape($user['UID']) . "', '"
					. sql_escape($date) . "', '" . sql_escape($ort) . "', " . "'"
					. sql_escape($bemerkung) . "')";
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

	$html .= "<p>" . Get_Text("Hello") . $user['Nick'] . ",<br />"
		. Get_Text("pub_wake_beschreibung") . "</p>\n\n";
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

	$html .= '</table><hr />' . Get_Text("pub_wake_Text2");

	$html .= template_render('../templates/user_wakeup.html', array (
		'wakeup_link'   => page_link_to("user_wakeup"),
		'date_text'     => Get_Text("pub_wake_Datum"),
		'date_value'    => date("Y-m-d H:i"),
		'place_text'    => Get_Text("pub_wake_Ort"),
		'comment_text'  => Get_Text("pub_wake_Bemerkung"),
		'comment_value' => "Knock knock Leo, follow the white rabbit to the blue tent",
		'submit_text'   => Get_Text("pub_wake_bouton")
	));
	return $html;
}
?>
