<?php


// Assistent zum Anlegen mehrerer neuer Schichten
function admin_shifts() {
	$msg = "";
	$ok = true;

	$name = "";
	$rid = 0;
	$start = DateTime :: createFromFormat("Y-m-d H:i", date("Y-m-d") . " 00:00")->getTimestamp();
	$end = $start +24 * 60 * 60;
	$mode = 'single';
	$angelmode = 'location';

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
		// Name/Bezeichnung der Schicht, darf nicht leer sein
		if (isset ($_REQUEST['name']) && strlen($_REQUEST['name']) > 0)
			$name = strip_request_item('name');
		else {
			$ok = false;
			$msg .= error("Gib bitte einen Namen für die Schicht(en) an.");
		}

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
				if (isset ($_REQUEST['change_hours']) && preg_match("/^([0-9]+(,|$))/", trim(str_replace(" ", "", $_REQUEST['change_hours'])))) {
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
				foreach ($types as $type) {
					if (isset ($_REQUEST['type_' . $type['TID']]) && preg_match("/^[0-9]+$/", trim($_REQUEST['type_' . $type['TID']]))) {
						$needed_angel_types[$type['TID']] = trim($_REQUEST['type_' . $type['TID']]);
					} else {
						$ok = false;
						$msg .= error("Bitte überprüfe die Eingaben für die benötigten Engel des Typs " . $type['Name'] . ".");
					}
				}
			} else {
				$ok = false;
				$msg .= error("Bitte Wähle einen Modus für die benötigten Engel.");
			}
		}
	}

	$room_select = html_select_key('rid', $room_array, '');
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
		'mode_variable_selected' => $_REQUEST['mode'] == 'variable' ? 'checked="checked"' : '',
		'angelmode_location_selected' => $_REQUEST['angelmode'] == 'location' ? 'checked="checked"' : '',
		'angelmode_manually_selected' => $_REQUEST['angelmode'] == 'manually' ? 'checked="checked"' : ''
	));
}
?>