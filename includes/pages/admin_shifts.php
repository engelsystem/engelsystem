<?php


// Assistent zum Anlegen mehrerer neuer Schichten
function admin_shifts() {
	$msg = "";
	$ok = true;

	$rid = 0;
	$start = DateTime :: createFromFormat("Y-m-d H:i", date("Y-m-d") . " 00:00")->getTimestamp();
	$end = $start +24 * 60 * 60;
	$mode = '';
	$angelmode = '';

	// Locations laden
	$rooms = sql_select("SELECT * FROM `Room` WHERE `show`='Y' ORDER BY `Name`");
	$room_array = array ();
	foreach ($rooms as $room)
		$room_array[$room['RID']] = $room['Name'];

	// Engeltypen laden
	$types = sql_select("SELECT * FROM `AngelTypes` ORDER BY `Name`");
	$needed_angel_types = array ();
	foreach ($types as $type)
		$needed_angel_types[$type['TID']] = 0;

	if (isset ($_REQUEST['preview'])) {
		// Name/Bezeichnung der Schicht, darf leer sein
		$name = strip_request_item('name');

		// Auswahl der sichtbaren Locations für die Schichten
		if (isset ($_REQUEST['rid']) && preg_match("/^[0-9]+$/", $_REQUEST['rid']) && isset ($room_array[$_REQUEST['rid']]))
			$rid = $_REQUEST['rid'];
		else {
			$ok = false;
			$rid = $rooms[0]['RID'];
			$msg .= error("Wähle bitte einen Raum aus.");
		}

		if (isset ($_REQUEST['start']) && $tmp = DateTime :: createFromFormat("Y-m-d H:i", trim($_REQUEST['start'])))
			$start = $tmp->getTimestamp();
		else {
			$ok = false;
			$msg .= error("Bitte gib einen Startzeitpunkt für die Schichten an.");
		}

		if (isset ($_REQUEST['end']) && $tmp = DateTime :: createFromFormat("Y-m-d H:i", trim($_REQUEST['end'])))
			$end = $tmp->getTimestamp();
		else {
			$ok = false;
			$msg .= error("Bitte gib einen Endzeitpunkt für die Schichten an.");
		}

		if ($start >= $end) {
			$ok = false;
			$msg .= error("Das Ende muss nach dem Startzeitpunkt liegen!");
		}

		if (isset ($_REQUEST['mode'])) {
			if ($_REQUEST['mode'] == 'single') {
				$mode = 'single';
			}
			elseif ($_REQUEST['mode'] == 'multi') {
				if (isset ($_REQUEST['length']) && preg_match("/^[0-9]+$/", trim($_REQUEST['length']))) {
					$mode = 'multi';
					$length = trim($_REQUEST['length']);
				} else {
					$ok = false;
					$msg .= error("Bitte gib eine Schichtlänge in Minuten an.");
				}
			}
			elseif ($_REQUEST['mode'] == 'variable') {
				if (isset ($_REQUEST['change_hours']) && preg_match("/^([0-9]{2}(,|$))/", trim(str_replace(" ", "", $_REQUEST['change_hours'])))) {
					$mode = 'variable';
					$change_hours = explode(",", $_REQUEST['change_hours']);
				} else {
					$ok = false;
					$msg .= error("Bitte gib die Schichtwechsel-Stunden kommagetrennt ein.");
				}
			}
		} else {
			$ok = false;
			$msg .= error("Bitte wähle einen Modus.");
		}

		if (isset ($_REQUEST['angelmode'])) {
			if ($_REQUEST['angelmode'] == 'location') {
				$angelmode = 'location';
			}
			elseif ($_REQUEST['angelmode'] == 'manually') {
				$angelmode = 'manually';
				foreach ($types as $type) {
					if (isset ($_REQUEST['type_' . $type['TID']]) && preg_match("/^[0-9]+$/", trim($_REQUEST['type_' . $type['TID']]))) {
						$needed_angel_types[$type['TID']] = trim($_REQUEST['type_' . $type['TID']]);
					} else {
						$ok = false;
						$msg .= error("Bitte überprüfe die Eingaben für die benötigten Engel des Typs " . $type['Name'] . ".");
					}
				}
				if (array_sum($needed_angel_types) == 0) {
					$ok = false;
					$msg .= error("Es werden 0 Engel benötigt. Bitte wähle benötigte Engel.");
				}
			} else {
				$ok = false;
				$msg .= error("Bitte Wähle einen Modus für die benötigten Engel.");
			}
		} else {
			$ok = false;
			$msg .= error("Bitte wähle benötigte Engel.");
		}

		// Alle Eingaben in Ordnung
		if ($ok) {
			if ($angelmode == 'location') {
				$needed_angel_types = array ();
				$needed_angel_types_location = sql_select("SELECT * FROM `NeededAngelTypes` WHERE `room_id`=" . sql_escape($rid));
				foreach ($needed_angel_types_location as $type)
					$needed_angel_types[$type['angel_type_id']] = $type['count'];
			}
			$shifts = array ();
			if ($mode == 'single') {
				$shifts[] = array (
					'start' => $start,
					'end' => $end,
					'RID' => $rid,
					'name' => $name
				);
			}
			elseif ($mode == 'multi') {
				$shift_start = $start;
				do {
					$shift_end = $shift_start + $length * 60;

					if ($shift_end > $end)
						$shift_end = $end;
					if ($shift_start >= $shift_end)
						break;

					$shifts[] = array (
						'start' => $shift_start,
						'end' => $shift_end,
						'RID' => $rid,
						'name' => $name
					);

					$shift_start = $shift_end;
				} while ($shift_end < $end);
			}
			elseif ($mode == 'variable') {
				rsort($change_hours);
				$day = DateTime :: createFromFormat("Y-m-d H:i", date("Y-m-d", $start) . " 00:00")->getTimestamp();
				$change_index = 0;
				// Ersten/nächsten passenden Schichtwechsel suchen
				foreach ($change_hours as $i => $change_hour) {
					if ($start < $day + $change_hour * 60 * 60)
						$change_index = $i;
					elseif ($start == $day + $change_hour * 60 * 60) {
						// Start trifft Schichtwechsel
						$change_index = ($i +count($change_hours) - 1) % count($change_hours);
						break;
					} else
						break;
				}

				$shift_start = $start;
				do {
					$day = DateTime :: createFromFormat("Y-m-d H:i", date("Y-m-d", $shift_start) . " 00:00")->getTimestamp();
					$shift_end = $day + $change_hours[$change_index] * 60 * 60;

					if ($shift_end > $end)
						$shift_end = $end;
					if ($shift_start >= $shift_end)
						$shift_end += 24 * 60 * 60;

					$shifts[] = array (
						'start' => $shift_start,
						'end' => $shift_end,
						'RID' => $rid,
						'name' => $name
					);

					$shift_start = $shift_end;
					$change_index = ($change_index +count($change_hours) - 1) % count($change_hours);
				} while ($shift_end < $end);
			}

			$shifts_table = "";
			foreach ($shifts as $shift) {
				$shifts_table .= '<tr><td>' . date("Y-m-d H:i", $shift['start']) . ' - ' . date("H:i", $shift['end']) . '<br />' . $room_array[$shift['RID']] . '</td>';
				$shifts_table .= '<td>' . $shift['name'];
				foreach ($types as $type) {
					if (isset ($needed_angel_types[$type['TID']]) && $needed_angel_types[$type['TID']] > 0)
						$shifts_table .= '<br /><b>' . $type['Name'] . ':</b> ' . $needed_angel_types[$type['TID']] . ' missing';
				}
				$shifts_table .= '</td></tr>';
			}

			// Fürs Anlegen zwischenspeichern:
			$_SESSION['admin_shifts_shifts'] = $shifts;
			$_SESSION['admin_shifts_types'] = $needed_angel_types;

			return template_render('../templates/admin_shift_preview.html', array (
				'shifts_table' => $shifts_table
			));
		}

	}
	elseif (isset ($_REQUEST['submit'])) {
		if (!is_array($_SESSION['admin_shifts_shifts']) || !is_array($_SESSION['admin_shifts_types'])) {
			header("Location: ?p=admin_shifts");
			die();
		}

		foreach ($_SESSION['admin_shifts_shifts'] as $shift) {
			sql_query("INSERT INTO `Shifts` SET `start`=" . sql_escape($shift['start']) . ",  `end`=" . sql_escape($shift['end']) . ", `RID`=" . sql_escape($shift['RID']) . ", `name`='" . sql_escape($shift['name']) . "'");
			$shift_id = sql_id();
			foreach ($_SESSION['admin_shifts_types'] as $type_id => $count) {
				sql_query("INSERT INTO `NeededAngelTypes` SET `shift_id`=" . sql_escape($shift_id) . ", `angel_type_id`=" . sql_escape($type_id) . ", `count`=" . sql_escape($count));
			}
		}

		$msg = success("Schichten angelegt.");
	} else {
		unset ($_SESSION['admin_shifts_shifts']);
		unset ($_SESSION['admin_shifts_types']);
	}

	$room_select = html_select_key('rid', $room_array, $_REQUEST['rid']);
	$angel_types = "";
	foreach ($types as $type) {
		$angel_types .= template_render('../templates/admin_shifts_angel_types.html', array (
			'id' => $type['TID'],
			'type' => $type['Name'],
			'value' => $needed_angel_types[$type['TID']]
		));
	}
	return template_render('../templates/admin_shifts.html', array (
		'angel_types' => $angel_types,
		'room_select' => $room_select,
		'msg' => $msg,
		'name' => $name,
		'start' => date("Y-m-d H:i", $start),
		'end' => date("Y-m-d H:i", $end),
		'mode_single_selected' => $_REQUEST['mode'] == 'single' ? 'checked="checked"' : '',
		'mode_multi_selected' => $_REQUEST['mode'] == 'multi' ? 'checked="checked"' : '',
		'mode_multi_length' => !empty($_REQUEST['length'])? $_REQUEST['length'] : '120',
		'mode_variable_selected' => $_REQUEST['mode'] == 'variable' ? 'checked="checked"' : '',
		'mode_variable_hours' => !empty($_REQUEST['change_hours'])? $_REQUEST['change_hours'] : '00, 04, 08, 10, 12, 14, 16, 18, 20, 22',
		'angelmode_location_selected' => $_REQUEST['angelmode'] == 'location' ? 'checked="checked"' : '',
		'angelmode_manually_selected' => $_REQUEST['angelmode'] == 'manually' ? 'checked="checked"' : ''
	));
}
?>
