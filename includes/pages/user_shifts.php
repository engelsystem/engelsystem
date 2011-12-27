<?php
function user_shifts() {
	global $user, $privileges;
	// Löschen einzelner Schicht-Einträge (Also Belegung einer Schicht von Engeln) durch Admins
	if (isset ($_REQUEST['entry_id']) && in_array('user_shifts_admin', $privileges)) {
		if (isset ($_REQUEST['entry_id']) && preg_match("/^[0-9]*$/", $_REQUEST['entry_id']))
			$entry_id = $_REQUEST['entry_id'];
		else
			header("Location: " . page_link_to('user_shifts'));

		sql_query("DELETE FROM `ShiftEntry` WHERE `id`=" . sql_escape($entry_id) . " LIMIT 1");
		return success("Der Schicht-Eintrag wurde gelöscht..", true);
	}
	// Schicht bearbeiten
	elseif (isset ($_REQUEST['edit_shift']) && in_array('admin_shifts', $privileges)) {
		$msg = "";
		$ok = true;

		if (isset ($_REQUEST['edit_shift']) && preg_match("/^[0-9]*$/", $_REQUEST['edit_shift']))
			$shift_id = $_REQUEST['edit_shift'];
		else
			header("Location: " . page_link_to('user_shifts'));

		if (sql_num_query("SELECT * FROM `ShiftEntry` WHERE `SID`=" . sql_escape($shift_id) . " LIMIT 1") > 0)
			return error("Du kannst nur Schichten bearbeiten, bei denen niemand eingetragen ist.", true);

		$shift = sql_select("SELECT * FROM `Shifts` JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `SID`=" . sql_escape($shift_id) . " LIMIT 1");
		if (count($shift) == 0)
			header("Location: " . page_link_to('user_shifts'));
		$shift = $shift[0];

		// Locations laden
		$rooms = sql_select("SELECT * FROM `Room` WHERE `show`='Y' ORDER BY `Name`");
		$room_array = array ();
		foreach ($rooms as $room)
			$room_array[$room['RID']] = $room['Name'];

		// Engeltypen laden
		$types = sql_select("SELECT `AngelTypes`.*, `NeededAngelTypes`.`count` FROM `NeededAngelTypes` JOIN `AngelTypes` ON (`NeededAngelTypes`.`angel_type_id` = `AngelTypes`.`id`) WHERE `shift_id`=" . sql_escape($shift_id) . " ORDER BY `AngelTypes`.`name`");
		$needed_angel_types = array ();
		foreach ($types as $type)
			$needed_angel_types[$type['id']] = $type['count'];

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

			foreach ($types as $type) {
				if (isset ($_REQUEST['type_' . $type['id']]) && preg_match("/^[0-9]+$/", trim($_REQUEST['type_' . $type['id']]))) {
					$needed_angel_types[$type['id']] = trim($_REQUEST['type_' . $type['id']]);
				} else {
					$ok = false;
					$msg .= error("Bitte überprüfe die Eingaben für die benötigten Engel des Typs " . $type['name'] . ".", true);
				}
			}
			if (array_sum($needed_angel_types) == 0) {
				$ok = false;
				$msg .= error("Es werden 0 Engel benötigt. Bitte wähle benötigte Engel.", true);
			}

			if ($ok) {
				sql_query("UPDATE `Shifts` SET `start`=" . sql_escape($start) . ", `end`=" . sql_escape($end) . ", `RID`=" . sql_escape($rid) . ", `name`='" . sql_escape($name) . "' WHERE `SID`=" . sql_escape($shift_id) . " LIMIT 1");
				sql_query("DELETE FROM `NeededAngelTypes` WHERE `shift_id`=" . sql_escape($shift_id));
				foreach ($needed_angel_types as $type_id => $count)
					sql_query("INSERT INTO `NeededAngelTypes` SET `shift_id`=" . sql_escape($shift_id) . ", `angel_type_id`=" . sql_escape($type_id) . ", `count`=" . sql_escape($count));
				return success("Schicht gespeichert.", true);
			}
		}

		$room_select = html_select_key('rid', 'rid', $room_array, $rid);
		$angel_types = "";
		foreach ($types as $type) {
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

			return success("Die Schicht wurde gelöscht.", true);
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
			if (in_array('user_shifts_admin', $privileges)) {
				if (isset ($_REQUEST['user_id']) && preg_match("/^[0-9]*$/", $_REQUEST['user_id']))
					$user_id = $_REQUEST['user_id'];
				else
					$user_id = $user['UID'];

				$user_test = sql_select("SELECT * FROM `User` WHERE `UID`=" . sql_escape($user_id) . " LIMIT 1");
				if (count($user_test) == 0)
					header("Location: " . page_link_to('user_shifts'));
			} else
				$user_id = $user['UID'];

			// TODO: Kollisionserkennung, andere Schichten zur gleichen Uhrzeit darf der Engel auch nicht belegt haben...
			$entries = sql_select("SELECT * FROM `ShiftEntry` WHERE `SID`=" . sql_escape($shift['SID']));
			foreach ($entries as $entry)
				if ($entry['UID'] == $user_id)
					return error("This angel does already have an entry for this shift.", true);

			$comment = strip_request_item_nl('comment');
			sql_query("INSERT INTO `ShiftEntry` SET `Comment`='" . sql_escape($comment) . "', `UID`=" . sql_escape($user_id) . ", `TID`=" . sql_escape($type_id) . ", `SID`=" . sql_escape($shift_id));
			return success("Du bist eingetragen. Danke!", true) . '<a href="' . page_link_to('user_myshifts') . '">Meine Schichten &raquo;</a>';
		}

		if (in_array('user_shifts_admin', $privileges)) {
			$users = sql_select("SELECT * FROM `User` ORDER BY `Nick`");
			$users_select = array ();
			foreach ($users as $usr)
				$users_select[$usr['UID']] = $usr['Nick'];
			$user_text = html_select_key('user_id', 'user_id', $users_select, $user['UID']);
		} else
			$user_text = $user['Nick'];

		return template_render('../templates/user_shifts_add.html', array (
			//'date' => date("Y-m-d H:i", $shift['start']) . ', ' . date("H:i", $shift['end'] - $shift['start']) . 'h',
			'date' => date("Y-m-d H:i", $shift['start']) . ', ' . shift_length($shift),
			'title' => $shift['name'],
			'location' => $shift['Name'],
			'angel' => $user_text,
			'type' => $type['name'],
			'comment' => ""
		));
	} else {
		$shifts = sql_select("SELECT COUNT(*) AS `count` FROM `Shifts` ORDER BY `start`");
		$days = array ();
		$rooms = array ();
		if (!isset ($_SESSION['user_shifts']))
			$_SESSION['user_shifts'] = array ();

		if ($shifts[0]["count"] > 0) {
			$days = sql_select("SELECT DISTINCT DATE(FROM_UNIXTIME(`start`)) FROM `Shifts`");
			$days = array_map('array_pop', $days);
			if (!isset ($_SESSION['user_shifts']['day']))
				$_SESSION['user_shifts']['day'] = $days[0];
			if (isset ($_REQUEST['day']))
				$_SESSION['user_shifts']['day'] = $_REQUEST['day'];

			$rooms = sql_select("SELECT * FROM `Room` WHERE `show`='Y' ORDER BY `Name`");
			if (!isset ($_SESSION['user_shifts']['id']))
				$_SESSION['user_shifts']['id'] = 0;
			if (isset ($_REQUEST['room_id']) && preg_match("/^[0-9]*$/", $_REQUEST['room_id']))
				$_SESSION['user_shifts']['id'] = $_REQUEST['room_id'];
			$day_timestamp = DateTime :: createFromFormat("Y-m-d-Hi", $_SESSION['user_shifts']['day'] . "-0000")->getTimestamp();

			if ($_SESSION['user_shifts']['id'] == 0)
				$shifts = sql_select("SELECT * FROM `Shifts` JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `start` > " . sql_escape(time()) . " ORDER BY `start`");
			else
				$shifts = sql_select("SELECT * FROM `Shifts` WHERE `RID`=" . sql_escape($_SESSION['user_shifts']['id']) . " AND `start` >= " . sql_escape($day_timestamp) . " AND `start` < " . sql_escape($day_timestamp +24 * 60 * 60) . " ORDER BY `start`");

			$shifts_table = "";
			$row_count = 0;
			foreach ($shifts as $shift) {
				$shift_row = '<tr><td>' . date(($_SESSION['user_shifts']['id'] == 0 ? "Y-m-d " : "") . "H:i", $shift['start']) . ' - ' . date("H:i", $shift['end']) . ($_SESSION['user_shifts']['id'] == 0 ? "<br />" . $shift['Name'] : "") . '</td><td>' . $shift['name'];
				if (in_array('admin_shifts', $privileges))
					$shift_row .= ' <a href="?p=user_shifts&edit_shift=' . $shift['SID'] . '">[edit]</a> <a href="?p=user_shifts&delete_shift=' . $shift['SID'] . '">[x]</a>';
				$shift_row .= '<br />';
				$show_shift = false;
				$angeltypes = sql_select("SELECT * FROM `NeededAngelTypes` JOIN `AngelTypes` ON (`NeededAngelTypes`.`angel_type_id` = `AngelTypes`.`id`) WHERE `shift_id`=" . sql_escape($shift['SID']) . " AND `count` > 0 ORDER BY `AngelTypes`.`name`");
				if (count($angeltypes) == 0)
					$angeltypes = sql_select("SELECT * FROM `NeededAngelTypes` JOIN `AngelTypes` ON (`NeededAngelTypes`.`angel_type_id` = `AngelTypes`.`id`) WHERE `room_id`=" . sql_escape($shift['RID']) . " AND `count` > 0 ORDER BY `AngelTypes`.`name`");

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
						if ($angeltype['count'] - count($entries) > 0)
							if (!$my_shift || in_array('user_shifts_admin', $privileges)) {
								$entry_list[] = '<a href="' . page_link_to('user_shifts') . '&shift_id=' . $shift['SID'] . '&type_id=' . $angeltype['id'] . '">' . ($angeltype['count'] - count($entries)) . ' Helfer' . ($angeltype['count'] - count($entries) != 1 ? '' : '') . ' gebraucht &raquo;</a>';
								$show_shift = true;
							} else
								$entry_list[] = ($angeltype['count'] - count($entries)) . ' Helfer gebraucht';

						$shift_row .= '<b>' . $angeltype['name'] . ':</b> ';
						$shift_row .= join(", ", $entry_list);
						$shift_row .= '<br />';
					}
				}
				if ($_SESSION['user_shifts']['id'] != 0 || ($show_shift && $row_count++ < 15))
					$shifts_table .= $shift_row . '</td></tr>';
			}
		}

		return template_render('../templates/user_shifts.html', array (
			'room_select' => make_room_select($rooms, $_SESSION['user_shifts']['id'], $_SESSION['user_shifts']['day']),
			'day_select' => make_day_select($days, $_SESSION['user_shifts']['day'], $_SESSION['user_shifts']['id']),
			'shifts_table' => $shifts_table
		));
	}
}

function make_day_select($days, $day, $id) {
	if ($id == 0)
		return "";
	$html = array ();
	foreach ($days as $d)
		$html[] = button(page_link_to('user_shifts') . '&day=' . $d, $d, $day == $d && $id != 0 ? 'on' : '');
	return buttons($html);
}

function make_room_select($rooms, $id, $day) {
	$html = array ();
	foreach ($rooms as $room) {
		$html[] = button(page_link_to('user_shifts') . '&room_id=' . $room['RID'], $room['Name'], $room['RID'] == $id ? 'on' : '');
	}
	$html[] = button(page_link_to('user_shifts') . '&room_id=0', "Next free shifts.", $id == 0 ? 'on' : '');
	return buttons($html);
}
?>
