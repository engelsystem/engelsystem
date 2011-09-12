<?php
function user_shifts() {
	global $user, $privileges;
	if (isset ($_REQUEST['entry_id']) && in_array('user_shifts_admin', $privileges)) {
		if (isset ($_REQUEST['entry_id']) && preg_match("/^[0-9]*$/", $_REQUEST['entry_id']))
			$shift_id = $_REQUEST['entry_id'];
		else
			header("Location: " . page_link_to('user_shifts'));

		sql_query("DELETE FROM `ShiftEntry` WHERE `id`=" . sql_escape($shift_id) . " LIMIT 1");
		return success("The shift entry has been deleted.");
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

		$type = sql_select("SELECT * FROM `AngelTypes` WHERE `TID`=" . sql_escape($type_id) . " LIMIT 1");
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
					return error("This angel does already have an entry for this shift.");

			$comment = strip_request_item_nl('comment');
			sql_query("INSERT INTO `ShiftEntry` SET `Comment`='" . sql_escape($comment) . "', `UID`=" . sql_escape($user_id) . ", `TID`=" . sql_escape($type_id) . ", `SID`=" . sql_escape($shift_id));
			return success("Now it's your shift. Thank you!") . '<a href="' . page_link_to('user_myshifts') . '">View my shifts &raquo;</a>';
		}

		if (in_array('user_shifts_admin', $privileges)) {
			$users = sql_select("SELECT * FROM `User` ORDER BY `Nick`");
			$users_select = array ();
			foreach ($users as $usr)
				$users_select[$usr['UID']] = $usr['Nick'];
			$user_text = html_select_key('user_id', $users_select, $user['UID']);
		} else
			$user_text = $user['Nick'];

		return template_render('../templates/user_shifts_add.html', array (
			'date' => date("Y-m-d H:i", $shift['start']) . ', ' . date("H:i", $shift['end'] - $shift['start']) . 'h',
			'title' => $shift['name'],
			'location' => $shift['Name'],
			'angel' => $user_text,
			'type' => $type['Name'],
			'comment' => ""
		));
	} else {
		$shifts = sql_select("SELECT * FROM `Shifts` ORDER BY `start`");
		$days = array ();
		foreach ($shifts as $shift)
			$days[] = date("Y-m-d", $shift['start']);
		$days = array_unique($days);
		$day = $days[0];
		if (isset ($_REQUEST['day']))
			$day = $_REQUEST['day'];

		$rooms = sql_select("SELECT * FROM `Room` WHERE `show`='Y' ORDER BY `Name`");
		$id = 0;
		if (isset ($_REQUEST['room_id']) && preg_match("/^[0-9]*$/", $_REQUEST['room_id']))
			$id = $_REQUEST['room_id'];
		$day_timestamp = DateTime :: createFromFormat("Y-m-d-Hi", $day . "-0000")->getTimestamp();

		if ($id == 0)
			$shifts = sql_select("SELECT * FROM `Shifts` JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `start` > " . sql_escape(time()) . " ORDER BY `start`");
		else
			$shifts = sql_select("SELECT * FROM `Shifts` WHERE `RID`=" . sql_escape($id) . " AND `start` >= " . sql_escape($day_timestamp) . " AND `start` < " . sql_escape($day_timestamp +24 * 60 * 60) . " ORDER BY `start`");

		$shifts_table = "";
		$row_count = 0;
		foreach ($shifts as $shift) {
			$shift_row = '<tr><td>' . date(($id == 0 ? "Y-m-d " : "") . "H:i", $shift['start']) . ' - ' . date("H:i", $shift['end']) . ($id == 0 ? "<br />" . $shift['Name'] : "") . '</td><td>' . $shift['name'] . '<br />';
			$show_shift = false;
			$angeltypes = sql_select("SELECT * FROM `NeededAngelTypes` JOIN `AngelTypes` ON (`NeededAngelTypes`.`angel_type_id` = `AngelTypes`.`TID`) WHERE `shift_id`=" . sql_escape($shift['SID']) . " AND `count` > 0 ORDER BY `AngelTypes`.`Name`");
			if (count($angeltypes) == 0)
				$angeltypes = sql_select("SELECT * FROM `NeededAngelTypes` JOIN `AngelTypes` ON (`NeededAngelTypes`.`angel_type_id` = `AngelTypes`.`TID`) WHERE `room_id`=" . sql_escape($shift['RID']) . " AND `count` > 0 ORDER BY `AngelTypes`.`Name`");

			if (count($angeltypes) > 0) {
				$my_shift = sql_num_query("SELECT * FROM `ShiftEntry` WHERE `SID`=" . sql_escape($shift['SID']) . " AND `UID`=" . sql_escape($user['UID']) . " LIMIT 1") > 0;
				foreach ($angeltypes as $angeltype) {
					$entries = sql_select("SELECT * FROM `ShiftEntry` JOIN `User` ON (`ShiftEntry`.`UID` = `User`.`UID`) WHERE `SID`=" . sql_escape($shift['SID']) . " AND `TID`=" . sql_escape($angeltype['TID']) . " ORDER BY `Nick`");
					$entry_list = array ();
					foreach ($entries as $entry) {
						if (in_array('user_shifts_admin', $privileges))
							$entry_list[] = $entry['Nick'] . ' <a href="' . page_link_to('user_shifts') . '&entry_id=' . $entry['id'] . '">[x]</a>';
						else
							$entry_list[] = $entry['Nick'];
					}
					if ($angeltype['count'] - count($entries) > 0)
						if (!$my_shift || in_array('user_shifts_admin', $privileges)) {
							$entry_list[] = '<a href="' . page_link_to('user_shifts') . '&shift_id=' . $shift['SID'] . '&type_id=' . $angeltype['TID'] . '">' . ($angeltype['count'] - count($entries)) . ' angel' . ($angeltype['count'] - count($entries) != 1 ? 's' : '') . ' missing &raquo;</a>';
							$show_shift = true;
						} else
							$entry_list[] = ($angeltype['count'] - count($entries)) . ' angel missing';

					$shift_row .= '<b>' . $angeltype['Name'] . ':</b> ';
					$shift_row .= join(", ", $entry_list);
					$shift_row .= '<br />';
				}
			}
			if ($id != 0 || ($show_shift && $row_count++ < 15))
				$shifts_table .= $shift_row . '</td></tr>';
		}

		return template_render('../templates/user_shifts.html', array (
			'room_select' => make_room_select($rooms, $id, $day),
			'day_select' => make_day_select($days, $day, $id),
			'shifts_table' => $shifts_table
		));
	}
}

function make_day_select($days, $day, $id) {
	$html = array ();
	foreach ($days as $d) {
		if ($day == $d && $id != 0)
			$html[] = '<b>' . $d . '</b>';
		else
			$html[] = '<a href="' . page_link_to('user_shifts') . '&day=' . $d . '&room_id=' . $id . '">' . $d . '</a>';
	}
	return join(' | ', $html);
}

function make_room_select($rooms, $id, $day) {
	$html = array ();
	foreach ($rooms as $room) {
		if ($room['RID'] == $id)
			$html[] = '<b>' . $room['Name'] . '</b>';
		else
			$html[] = '<a href="' . page_link_to('user_shifts') . '&room_id=' . $room['RID'] . '&day=' . $day . '">' . $room['Name'] . '</a>';
	}
	if ($id == 0)
		$html[] = '<b>Next 15 free shifts</b>';
	else
		$html[] = '<a href="' . page_link_to('user_shifts') . '&room_id=0">Next 15 free shifts</a>';
	return join(' | ', $html);
}
?>
