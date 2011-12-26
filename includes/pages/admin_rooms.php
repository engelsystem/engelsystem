<?php
function admin_rooms() {
	global $user;

	$html = "";
	$rooms = sql_select("SELECT * FROM `Room` ORDER BY `Number`, `Name`");
	if (!isset ($_REQUEST["action"])) {
		$html .= "Hallo " . $user['Nick'] .
		",<br />\nhier hast du die M&ouml;glichkeit, neue R&auml;ume f&uuml;r die Schichtpl&auml;ne einzutragen " .
		"oder vorhandene abzu&auml;ndern:<br /><br />\n";

		// Räume auflisten
		if (count($rooms) > 0) {
			$html .= '<table><thead><tr>';

			$html .= "<table width=\"100%\" class=\"border\" cellpadding=\"2\" cellspacing=\"1\">\n";
			$html .= "<tr class=\"contenttopic\">\n";

			// Tabellenüberschriften generieren
			foreach ($rooms[0] as $attr => $tmp)
				if ($attr != 'RID')
					$html .= '<th>' . $attr . '</th>';
			$html .= '<th>&nbsp;</th>';
			$html .= '</tr></thead><tbody>';

			foreach ($rooms as $i => $room) {
				$html .= '<tr>';
				foreach ($room as $attr => $value)
					if ($attr != 'RID')
						$html .= '<td>' . $value . '</td>';
				$html .= '<td><a href="' . page_link_to("admin_rooms") . '&action=change&RID=' . $room['RID'] . '">Edit</a></td>';
				$html .= '</tr>';
			}

			$html .= '</tbody></table>';
		}
		$html .= "<hr /><a href=\"" . page_link_to("admin_rooms") . "&action=new\">Neuen Raum/Ort eintragen</a><br />\n";
	} else {
		switch ($_REQUEST["action"]) {

			case 'new' :
				$html .= template_render('../templates/admin_rooms_new_form.html', array (
					'link' => page_link_to("admin_rooms")
				));
				break;

			case 'newsave' :
				$name = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui", '', strip_tags($_REQUEST['Name']));
				$man = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui", '', strip_tags($_REQUEST['Man']));
				$from_pentabarf = preg_replace("/([^YN]{1,})/ui", '', strip_tags($_REQUEST['FromPentabarf']));
				$show = preg_replace("/([^YN]{1,})/ui", '', strip_tags($_REQUEST['Show']));
				$number = preg_replace("/([^0-9]{1,})/ui", '', strip_tags($_REQUEST['Number']));
				sql_query("INSERT INTO `Room` SET `Name`='" . sql_escape($name) . "', `Man`='" . sql_escape($man) . "', `FromPentabarf`='" . sql_escape($from_pentabarf) . "', `show`='" . sql_escape($show) . "', `Number`='" . sql_escape($number) . "'");
				header("Location: " . page_link_to("admin_rooms"));
				break;

			case 'change' :
				if (isset ($_REQUEST['RID']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['RID']))
					$rid = $_REQUEST['RID'];
				else
					return error("Incomplete call, missing Room ID.", true);

				$room = sql_select("SELECT * FROM `Room` WHERE `RID`=" . sql_escape($rid) . " LIMIT 1");
				if (count($room) > 0) {
					list ($room) = $room;
					$room_angel_types = sql_select("SELECT `AngelTypes`.*, `NeededAngelTypes`.`count` FROM `AngelTypes` LEFT OUTER JOIN `NeededAngelTypes` ON (`AngelTypes`.`id` = `NeededAngelTypes`.`angel_type_id` AND `NeededAngelTypes`.`room_id`=" . sql_escape($rid) . ") ORDER BY `AngelTypes`.`name`");

					$angel_types = "";
					foreach ($room_angel_types as $room_angel_type) {
						if ($room_angel_type['count'] == "")
							$room_angel_type['count'] = "0";
						$angel_types .= '<tr><td>' . $room_angel_type['name'] . '</td><td><input type="text" name="angel_type_' . $room_angel_type['id'] . '" value="' . $room_angel_type['count'] . '" /></td></tr>';
					}

					$html .= template_render('../templates/admin_rooms_edit_form.html', array (
						'link' => page_link_to("admin_rooms"),
						'room_id' => $rid,
						'name' => $room['Name'],
						'man' => $room['Man'],
						'number' => $room['Number'],
						'from_pentabarf_options' => html_options('FromPentabarf', array (
							'Y' => 'Yes',
							'N' => 'No'
						), $room['FromPentabarf']),
						'show_options' => html_options('Show', array (
							'Y' => 'Yes',
							'N' => 'No'
						), $room['show']),
						'angel_types' => $angel_types
					));
				} else
					return error("No Room found.", true);
				break;

			case 'changesave' :
				if (isset ($_REQUEST['RID']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['RID']))
					$rid = $_REQUEST['RID'];
				else
					return error("Incomplete call, missing Room ID.", true);

				$room = sql_select("SELECT * FROM `Room` WHERE `RID`=" . sql_escape($rid) . " LIMIT 1");
				if (count($room) > 0) {
					list ($room) = $room;
					$room_angel_types = sql_select("SELECT `AngelTypes`.* FROM `AngelTypes` LEFT OUTER JOIN `NeededAngelTypes` ON (`AngelTypes`.`id` = `NeededAngelTypes`.`angel_type_id` AND `NeededAngelTypes`.`room_id`=" . sql_escape($rid) . ") ORDER BY `AngelTypes`.`name`");

					$name = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui", '', strip_tags($_REQUEST['Name']));
					$man = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}]{1,})/ui", '', strip_tags($_REQUEST['Man']));
					$from_pentabarf = preg_replace("/([^YN]{1,})/ui", '', strip_tags($_REQUEST['FromPentabarf']));
					$show = preg_replace("/([^YN]{1,})/ui", '', strip_tags($_REQUEST['Show']));
					$number = preg_replace("/([^0-9]{1,})/ui", '', strip_tags($_REQUEST['Number']));
					sql_query("UPDATE `Room` SET `Name`='" . sql_escape($name) . "', `Man`='" . sql_escape($man) . "', `FromPentabarf`='" . sql_escape($from_pentabarf) . "', `show`='" . sql_escape($show) . "', `Number`='" . sql_escape($number) . "' WHERE `RID`=" . sql_escape($rid) . " LIMIT 1");
					sql_query("DELETE FROM `NeededAngelTypes` WHERE `room_id`=" . sql_escape($rid));
					foreach ($room_angel_types as $room_angel_type) {
						if (isset ($_REQUEST['angel_type_' . $room_angel_type['id']]) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['angel_type_' . $room_angel_type['id']]))
							$count = $_REQUEST['angel_type_' . $room_angel_type['id']];
						else
							$count = "0";
						sql_query("INSERT INTO `NeededAngelTypes` SET `room_id`=" . sql_escape($rid) . ", `angel_type_id`=" . sql_escape($room_angel_type['id']) . ", `count`=" . sql_escape($count));
					}
					header("Location: " . page_link_to("admin_rooms"));
				} else
					return error("No Room found.", true);
				break;

			case 'delete' :
				if (isset ($_REQUEST['RID']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['RID']))
					$rid = $_REQUEST['RID'];
				else
					return error("Incomplete call, missing Room ID.", true);

				if (sql_num_query("SELECT * FROM `Room` WHERE `RID`=" . sql_escape($rid) . " LIMIT 1") > 0) {
					sql_query("DELETE FROM `Room` WHERE `RID`=" . sql_escape($rid) . " LIMIT 1");
					sql_query("DELETE FROM `NeededAngelTypes` WHERE `room_id`=" . sql_escape($rid) . " LIMIT 1");
					header("Location: " . page_link_to("admin_rooms"));
				} else
					return error("No Room found.", true);
				break;

		}
	}
	return $html;
}
?>
