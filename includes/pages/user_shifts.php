<?php
function user_shifts() {
	global $user, $privileges;

	// Löschen einzelner Schicht-Einträge (Also Belegung einer Schicht von Engeln) durch Admins
	if (isset ($_REQUEST['entry_id']) && in_array('user_shifts_admin', $privileges)) {
		if (isset ($_REQUEST['entry_id']) && test_request_int('entry_id'))
			$entry_id = $_REQUEST['entry_id'];
		else
			redirect(page_link_to('user_shifts'));

		sql_query("DELETE FROM `ShiftEntry` WHERE `id`=" . sql_escape($entry_id) . " LIMIT 1");
		success("Der Schicht-Eintrag wurde gelöscht.");
		redirect(page_link_to('user_shifts'));
	}
	// Schicht bearbeiten
	elseif (isset ($_REQUEST['edit_shift']) && in_array('admin_shifts', $privileges)) {
		$msg = "";
		$ok = true;

		if (isset ($_REQUEST['edit_shift']) && test_request_int('edit_shift'))
			$shift_id = $_REQUEST['edit_shift'];
		else
			redirect(page_link_to('user_shifts'));

		if (sql_num_query("SELECT * FROM `ShiftEntry` WHERE `SID`=" . sql_escape($shift_id) . " LIMIT 1") > 0) {
			error("Du kannst nur Schichten bearbeiten, bei denen niemand eingetragen ist.");
			redirect(page_link_to('user_shift'));
		}

		$shift = sql_select("SELECT * FROM `Shifts` JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `SID`=" . sql_escape($shift_id) . " LIMIT 1");
		if (count($shift) == 0)
			redirect(page_link_to('user_shifts'));
		$shift = $shift[0];

		// Locations laden
		$rooms = sql_select("SELECT * FROM `Room` WHERE `show`='Y' ORDER BY `Name`");
		$room_array = array ();
		foreach ($rooms as $room)
			$room_array[$room['RID']] = $room['Name'];

		// Engeltypen laden
		$needed_angel_types_source = sql_select("SELECT `AngelTypes`.*, `NeededAngelTypes`.`count` FROM `AngelTypes` LEFT JOIN `NeededAngelTypes` ON (`NeededAngelTypes`.`angel_type_id` = `AngelTypes`.`id` AND `NeededAngelTypes`.`shift_id`=" . sql_escape($shift_id) . ") ORDER BY `AngelTypes`.`name`");
		$needed_angel_types = array ();
		foreach ($needed_angel_types_source as $type)
			$needed_angel_types[$type['id']] = $type['count'] != "" ? $type['count'] : "0";

		$name = $shift['name'];
		$rid = $shift['RID'];
		$start = $shift['start'];
		$end = $shift['end'];

		if (isset ($_REQUEST['submit'])) {
			// Name/Bezeichnung der Schicht, darf leer sein
			$name = strip_request_item('name');

			// Auswahl der sichtbaren Locations für die Schichten
			if (isset ($_REQUEST['rid']) && preg_match("/^[0-9]+$/", $_REQUEST['rid']) && isset ($room_array[$_REQUEST['rid']]))
				$rid = $_REQUEST['rid'];
			else {
				$ok = false;
				$rid = $rooms[0]['RID'];
				$msg .= error("Wähle bitte einen Raum aus.", true);
			}

			if (isset ($_REQUEST['start']) && $tmp = DateTime :: createFromFormat("Y-m-d H:i", trim($_REQUEST['start'])))
				$start = $tmp->getTimestamp();
			else {
				$ok = false;
				$msg .= error("Bitte gib einen Startzeitpunkt für die Schichten an.", true);
			}

			if (isset ($_REQUEST['end']) && $tmp = DateTime :: createFromFormat("Y-m-d H:i", trim($_REQUEST['end'])))
				$end = $tmp->getTimestamp();
			else {
				$ok = false;
				$msg .= error("Bitte gib einen Endzeitpunkt für die Schichten an.", true);
			}

			if ($start >= $end) {
				$ok = false;
				$msg .= error("Das Ende muss nach dem Startzeitpunkt liegen!", true);
			}

			foreach ($needed_angel_types_source as $type) {
				if (isset ($_REQUEST['type_' . $type['id']]) && preg_match("/^[0-9]+$/", trim($_REQUEST['type_' . $type['id']]))) {
					$needed_angel_types[$type['id']] = trim($_REQUEST['type_' . $type['id']]);
				} else {
					$ok = false;
					$msg .= error("Bitte überprüfe die Eingaben für die benötigten Engel des Typs " . $type['name'] . ".", true);
				}
			}

			if ($ok) {
				sql_query("UPDATE `Shifts` SET `start`=" . sql_escape($start) . ", `end`=" . sql_escape($end) . ", `RID`=" . sql_escape($rid) . ", `name`='" . sql_escape($name) . "' WHERE `SID`=" . sql_escape($shift_id) . " LIMIT 1");
				sql_query("DELETE FROM `NeededAngelTypes` WHERE `shift_id`=" . sql_escape($shift_id));
				foreach ($needed_angel_types as $type_id => $count)
					sql_query("INSERT INTO `NeededAngelTypes` SET `shift_id`=" . sql_escape($shift_id) . ", `angel_type_id`=" . sql_escape($type_id) . ", `count`=" . sql_escape($count));
				success("Schicht gespeichert.");
				redirect(page_link_to('user_shifts'));
			}
		}

		$room_select = html_select_key('rid', 'rid', $room_array, $rid);
		$angel_types = "";
		foreach ($needed_angel_types_source as $type) {
			$angel_types .= template_render('../templates/admin_shifts_angel_types.html', array (
				'id' => $type['id'],
				'type' => $type['name'],
				'value' => $needed_angel_types[$type['id']]
			));
		}
		return template_render('../templates/user_shifts_edit.html', array (
			'msg' => $msg,
			'name' => $name,
			'room_select' => $room_select,
			'start' => date("Y-m-d H:i", $start),
			'end' => date("Y-m-d H:i", $end),
			'angel_types' => $angel_types
		));
	}
	// Schicht komplett löschen (nur für admins/user mit user_shifts_admin privileg)
	elseif (isset ($_REQUEST['delete_shift']) && in_array('user_shifts_admin', $privileges)) {
		if (isset ($_REQUEST['delete_shift']) && preg_match("/^[0-9]*$/", $_REQUEST['delete_shift']))
			$shift_id = $_REQUEST['delete_shift'];
		else
			header("Location: " . page_link_to('user_shifts'));

		$shift = sql_select("SELECT * FROM `Shifts` JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `SID`=" . sql_escape($shift_id) . " LIMIT 1");
		if (count($shift) == 0)
			header("Location: " . page_link_to('user_shifts'));
		$shift = $shift[0];

		// Schicht löschen bestätigt
		if (isset ($_REQUEST['delete'])) {
			sql_query("DELETE FROM `ShiftEntry` WHERE `SID`=" . sql_escape($shift_id));
			sql_query("DELETE FROM `NeededAngelTypes` WHERE `shift_id`=" . sql_escape($shift_id));
			sql_query("DELETE FROM `Shifts` WHERE `SID`=" . sql_escape($shift_id) . " LIMIT 1");

			success("Die Schicht wurde gelöscht.");
			redirect(page_link_to('user_shifts'));
		}

		return template_render('../templates/user_shifts_admin_delete.html', array (
			'name' => $shift['name'],
			'start' => date("Y-m-d H:i", $shift['start']),
			'end' => date("H:i", $shift['end']),
			'id' => $shift_id
		));
	}
	elseif (isset ($_REQUEST['shift_id'])) {
		if (isset ($_REQUEST['shift_id']) && preg_match("/^[0-9]*$/", $_REQUEST['shift_id']))
			$shift_id = $_REQUEST['shift_id'];
		else
			header("Location: " . page_link_to('user_shifts'));

		$shift = sql_select("SELECT * FROM `Shifts` JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `SID`=" . sql_escape($shift_id) . " LIMIT 1");
		if (count($shift) == 0)
			header("Location: " . page_link_to('user_shifts'));
		$shift = $shift[0];

		if (isset ($_REQUEST['type_id']) && preg_match("/^[0-9]*$/", $_REQUEST['type_id']))
			$type_id = $_REQUEST['type_id'];
		else
			header("Location: " . page_link_to('user_shifts'));

		$type = sql_select("SELECT * FROM `AngelTypes` WHERE `id`=" . sql_escape($type_id) . " LIMIT 1");
		if (count($type) == 0)
			header("Location: " . page_link_to('user_shifts'));
		$type = $type[0];

		if (isset ($_REQUEST['submit'])) {
			$selected_type_id = $type_id;
			if (in_array('user_shifts_admin', $privileges)) {
				if (isset ($_REQUEST['user_id']) && preg_match("/^[0-9]*$/", $_REQUEST['user_id']))
					$user_id = $_REQUEST['user_id'];
				else
					$user_id = $user['UID'];

				if (sql_num_query("SELECT * FROM `User` WHERE `UID`=" . sql_escape($user_id) . " LIMIT 1") == 0)
					redirect(page_link_to('user_shifts'));

				if (isset ($_REQUEST['angeltype_id']) && test_request_int('angeltype_id') && sql_num_query("SELECT * FROM `AngelTypes` WHERE `id`=" . sql_escape($_REQUEST['angeltype_id']) . " LIMIT 1") > 0)
					$selected_type_id = $_REQUEST['angeltype_id'];
			} else
				$user_id = $user['UID'];

			// TODO: Kollisionserkennung, andere Schichten zur gleichen Uhrzeit darf der Engel auch nicht belegt haben...
			$entries = sql_select("SELECT * FROM `ShiftEntry` WHERE `SID`=" . sql_escape($shift['SID']));
			foreach ($entries as $entry)
				if ($entry['UID'] == $user_id)
					return error("This angel does already have an entry for this shift.", true);

			$comment = strip_request_item_nl('comment');
			sql_query("INSERT INTO `ShiftEntry` SET `Comment`='" . sql_escape($comment) . "', `UID`=" . sql_escape($user_id) . ", `TID`=" . sql_escape($selected_type_id) . ", `SID`=" . sql_escape($shift_id));

			success("Du bist eingetragen. Danke!" . ' <a href="' . page_link_to('user_myshifts') . '">Meine Schichten &raquo;</a>');
			redirect(page_link_to('user_shifts'));
		}

		if (in_array('user_shifts_admin', $privileges)) {
			$users = sql_select("SELECT * FROM `User` ORDER BY `Nick`");
			$users_select = array ();
			foreach ($users as $usr)
				$users_select[$usr['UID']] = $usr['Nick'];
			$user_text = html_select_key('user_id', 'user_id', $users_select, $user['UID']);

			$angeltypes_source = sql_select("SELECT * FROM `AngelTypes` ORDER BY `name`");
			$angeltypes = array ();
			foreach ($angeltypes_source as $angeltype)
				$angeltypes[$angeltype['id']] = $angeltype['name'];
			$angeltyppe_select = html_select_key('angeltype_id', 'angeltype_id', $angeltypes, $type['id']);
		} else {
			$user_text = $user['Nick'];
			$angeltyppe_select = $type['name'];
		}

		return template_render('../templates/user_shifts_add.html', array (
			//'date' => date("Y-m-d H:i", $shift['start']) . ', ' . date("H:i", $shift['end'] - $shift['start']) . 'h',
			'date' => date("Y-m-d H:i", $shift['start']) . ', ' . shift_length($shift),
			'title' => $shift['name'],
			'location' => $shift['Name'],
			'angel' => $user_text,
			'type' => $angeltyppe_select,
			'comment' => ""
		));
	} else {
		return view_user_shifts();
	}
}

function view_user_shifts() {
	global $user, $privileges;
	global $ical_shifts;

	$ical_shifts = array ();
	$days = sql_select("SELECT DISTINCT DATE(FROM_UNIXTIME(`start`)) AS `id`, DATE(FROM_UNIXTIME(`start`)) AS `name` FROM `Shifts`");
	$rooms = sql_select("SELECT `RID` AS `id`, `Name` AS `name` FROM `Room` WHERE `show`='Y' ORDER BY `Name`");
	$types = sql_select("SELECT `id`, `name` FROM `AngelTypes`");
	$filled = array (
		array (
			'id' => '1',
			'name' => 'Volle'
		),
		array (
			'id' => '0',
			'name' => 'Freie'
		)
	);

	if (!isset ($_SESSION['user_shifts']))
		$_SESSION['user_shifts'] = array ();

	if (!isset ($_SESSION['user_shifts']['filled'])) {
		$_SESSION['user_shifts']['filled'] = array (
			0
		);
	}

	foreach (array (
			'rooms',
			'types',
			'filled'
		) as $key) {
		if (isset ($_REQUEST[$key])) {
			$filtered = array_filter($_REQUEST[$key], 'is_numeric');
			if (!empty ($filtered))
				$_SESSION['user_shifts'][$key] = $filtered;
			unset ($filtered);
		}
		if (!isset ($_SESSION['user_shifts'][$key]))
			$_SESSION['user_shifts'][$key] = array_map('get_ids_from_array', $$key);
	}

	if (isset ($_REQUEST['days'])) {
		$filtered = array_filter($_REQUEST['days'], create_function('$a', 'return preg_match("/^\d\d\d\d-\d\d-\d\d\\$/", $a);'));
		if (!empty ($filtered))
			$_SESSION['user_shifts']['days'] = $filtered;
		unset ($filtered);
	}
	if (!isset ($_SESSION['user_shifts']['days']))
		$_SESSION['user_shifts']['days'] = array (
			date('Y-m-d')
		);
	if (!isset ($_SESSION['user_shifts']['rooms']) || count($_SESSION['user_shifts']['rooms']) == 0)
	  $_SESSION['user_shifts']['rooms'] = array(0);

	$shifts = sql_select("SELECT `Shifts`.*, `Room`.`Name` as `room_name` FROM `Shifts` JOIN `Room` USING (`RID`)
																					WHERE `Shifts`.`RID` IN (" . implode(',', $_SESSION['user_shifts']['rooms']) . ")
																						AND DATE(FROM_UNIXTIME(`start`)) IN ('" . implode("','", $_SESSION['user_shifts']['days']) . "')
																					ORDER BY `start`");

	$shifts_table = "";
	$row_count = 0;
	foreach ($shifts as $shift) {
		$info = array ();
		if (count($_SESSION['user_shifts']['days']) > 1)
			$info[] = date("Y-m-d", $shift['start']);
		$info[] = date("H:i", $shift['start']) . ' - ' . date("H:i", $shift['end']);
		if (count($_SESSION['user_shifts']['rooms']) > 1)
			$info[] = $shift['room_name'];
		$shift_row = '<tr><td>' . join('<br />', $info) . '</td>';
		$shift_row .= '<td>' . $shift['name'];

		if (in_array('admin_shifts', $privileges))
			$shift_row .= ' <a href="?p=user_shifts&edit_shift=' . $shift['SID'] . '">[edit]</a> <a href="?p=user_shifts&delete_shift=' . $shift['SID'] . '">[x]</a>';
		$shift_row .= '<br />';
		$is_free = false;
		$shift_has_special_needs = 0 < sql_num_query("SELECT `id` FROM `NeededAngelTypes` WHERE `shift_id` = " . $shift['SID']);
		$query = "SELECT *
																																			FROM `NeededAngelTypes`
																																			JOIN `AngelTypes`
																																				ON (`NeededAngelTypes`.`angel_type_id` = `AngelTypes`.`id`)
																																			WHERE ";
		if ($shift_has_special_needs)
			$query .= "`shift_id` = " . sql_escape($shift['SID']);
		else
			$query .= "`room_id` = " . sql_escape($shift['RID']);
		$query .= "		AND `count` > 0
																																				AND `angel_type_id` IN (" . implode(',', $_SESSION['user_shifts']['types']) . ")
																																			ORDER BY `AngelTypes`.`name`";
		$angeltypes = sql_select($query);

		if (count($angeltypes) > 0) {
			$my_shift = sql_num_query("SELECT * FROM `ShiftEntry` WHERE `SID`=" . sql_escape($shift['SID']) . " AND `UID`=" . sql_escape($user['UID']) . " LIMIT 1") > 0;
			foreach ($angeltypes as $angeltype) {
				$entries = sql_select("SELECT * FROM `ShiftEntry` JOIN `User` ON (`ShiftEntry`.`UID` = `User`.`UID`) WHERE `SID`=" . sql_escape($shift['SID']) . " AND `TID`=" . sql_escape($angeltype['id']) . " ORDER BY `Nick`");
				$entry_list = array ();
				foreach ($entries as $entry) {
					if (in_array('user_shifts_admin', $privileges))
						$entry_list[] = '<a href="' . page_link_to('user_myshifts') . '&id=' . $entry['UID'] . '">' . $entry['Nick'] . '</a> <a href="' . page_link_to('user_shifts') . '&entry_id=' . $entry['id'] . '">[x]</a>';
					else
						$entry_list[] = $entry['Nick'];
				}
				if ($angeltype['count'] - count($entries) > 0) {
					if (!$my_shift || in_array('user_shifts_admin', $privileges)) {
						$entry_list[] = '<a href="' . page_link_to('user_shifts') . '&shift_id=' . $shift['SID'] . '&type_id=' . $angeltype['id'] . '">' . ($angeltype['count'] - count($entries)) . ' Helfer' . ($angeltype['count'] - count($entries) != 1 ? '' : '') . ' gebraucht &raquo;</a>';
					} else {
						$entry_list[] = ($angeltype['count'] - count($entries)) . ' Helfer gebraucht';
					}
					$is_free = true;
				}

				$shift_row .= '<b>' . $angeltype['name'] . ':</b> ';
				$shift_row .= join(", ", $entry_list);
				$shift_row .= '<br />';
			}
			if (in_array('user_shifts_admin', $privileges)) {
				$shift_row .= '<a href="' . page_link_to('user_shifts') . '&shift_id=' . $shift['SID'] . '&type_id=' . $angeltype['id'] . '">Weitere Helfer eintragen &raquo;</a>';
			}
			if (($is_free && in_array(0, $_SESSION['user_shifts']['filled'])) || (!$is_free && in_array(1, $_SESSION['user_shifts']['filled']))) {
				$shifts_table .= $shift_row . '</td></tr>';
				$row_count++;
				$ical_shifts[] = $shift;
			}
		}
	}

	if ($user['ical_key'] == "")
		user_reset_ical_key($user);

	return msg() . template_render('../templates/user_shifts.html', array (
		'room_select' => make_select($rooms, $_SESSION['user_shifts']['rooms'], "rooms", "Räume"),
		'day_select' => make_select($days, $_SESSION['user_shifts']['days'], "days", "Tage"),
		'type_select' => make_select($types, $_SESSION['user_shifts']['types'], "types", "Aufgaben"),
		'filled_select' => make_select($filled, $_SESSION['user_shifts']['filled'], "filled", "Besetzung"),
		'shifts_table' => $shifts_table,
		'ical_link' => make_user_shifts_ical_link($user['ical_key']),
		'reset_link' => page_link_to('user_myshifts') . '&reset'
	));
}

function make_user_shifts_ical_link($key) {
	$link = "";
	foreach ($_SESSION['user_shifts']['rooms'] as $room)
		$link .= '&rooms[]=' . $room;
	foreach ($_SESSION['user_shifts']['days'] as $day)
		$link .= '&days[]=' . $day;
	foreach ($_SESSION['user_shifts']['types'] as $type)
		$link .= '&types[]=' . $type;
	foreach ($_SESSION['user_shifts']['filled'] as $filled)
		$link .= '&filled[]=' . $filled;
	return page_link_to_absolute('ical') . $link . '&export=user_shifts&key=' . $key;
}

function get_ids_from_array($array) {
	return $array["id"];
}

function make_select($items, $selected, $name, $title = null) {
	$html_items = array ();
	if (isset ($title))
		$html_items[] = '<li class="heading">' . $title . '</li>' . "\n";

	foreach ($items as $i)
		$html_items[] = '<li><label><input type="checkbox" name="' . $name . '[]" value="' . $i['id'] . '"' . (in_array($i['id'], $selected) ? ' checked="checked"' : '') . '> ' . $i['name'] . '</label></li>';
	$html = '<div class="selection ' . $name . '">' . "\n";
	$html .= '<ul id="selection_' . $name . '">' . "\n";
	$html .= implode("\n", $html_items);
	$html .= '</ul>' . "\n";
	$html .= buttons(array (
		button("javascript: check_all('selection_" . $name . "')", "Alle", ""),
		button("javascript: uncheck_all('selection_" . $name . "')", "Keine", "")
	));
	$html .= '</div>' . "\n";
	return $html;
}
?>
