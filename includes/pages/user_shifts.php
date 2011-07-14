<?php
function user_shifts() {
	global $user, $privileges;
	if (isset ($_REQUEST['shift_id'])) {

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
			$comment = strip_request_item_nl($_REQUEST['comment']);
			sql_query("INSERT INTO `ShiftEntry` SET `UID`=" . sql_escape($user['UID']) . ", `TID`=" . sql_escape($type_id) . ", `SID`=" . sql_escape($shift_id));
			return success("Now it's your shift. Thank you!") . '<a href="' . page_link_to('user_myshifts') . '">View my shifts &raquo;</a>';
		}

		return template_render('../templates/user_shifts_add.html', array (
			'date' => date("Y-m-d H:i", $shift['start']) . ', ' . date("H:i", $shift['end'] - $shift['start']) . 'h',
			'title' => $shift['name'],
			'location' => $shift['Name'],
			'angel' => $user['Nick'],
			'type' => $type['Name']
		));
	} else {
		$shifts = sql_select("SELECT * FROM `Shifts` ORDER BY `start`");
		$days = array ();
		foreach ($shifts as $shift)
			$days[] = date("Y-m-d", $shift['start']);
		$days = array_unique($days);
		if (count($days) == 0)
			return "None";
		$day = $days[0];
		if (isset ($_REQUEST['day']))
			$day = $_REQUEST['day'];

		$rooms = sql_select("SELECT * FROM `Room` WHERE `show`='Y' ORDER BY `Name`");
		if (count($rooms) == 0)
			return "None";
		$id = $rooms[0]['RID'];
		if (isset ($_REQUEST['room_id']) && preg_match("/^[0-9]*$/", $_REQUEST['room_id']))
			$id = $_REQUEST['room_id'];
		$day_timestamp = DateTime :: createFromFormat("Y-m-d-Hi", $day . "-0000")->getTimestamp();
		$shifts = sql_select("SELECT * FROM `Shifts` WHERE `RID`=" . sql_escape($id) . " AND `start` >= " . sql_escape($day_timestamp) . " AND `start` < " . sql_escape($day_timestamp +24 * 60 * 60) . " ORDER BY `start`");

		$shifts_table = "";
		foreach ($shifts as $shift) {
			$shifts_table .= '<tr><td>' . date("H:i", $shift['start']) . ' - ' . date("H:i", $shift['end']) . '</td><td>' . $shift['name'] . '<br />';
			$angeltypes = sql_select("SELECT * FROM `RoomAngelTypes` JOIN `AngelTypes` ON (`RoomAngelTypes`.`angel_type_id` = `AngelTypes`.`TID`) WHERE `room_id`=" . sql_escape($id) . " AND `count` > 0 ORDER BY `AngelTypes`.`Name`");
			if (count($angeltypes) > 0) {
				$my_shift = sql_num_query("SELECT * FROM `ShiftEntry` WHERE `SID`=" . sql_escape($shift['SID']) . " AND `UID`=" . sql_escape($user['UID']) . " LIMIT 1") > 0;
				foreach ($angeltypes as $angeltype) {
					$shifts_table .= '<b>' . $angeltype['Name'] . ':</b> ';
					$entries = sql_select("SELECT * FROM `ShiftEntry` JOIN `User` ON (`ShiftEntry`.`UID` = `User`.`UID`) WHERE `SID`=" . sql_escape($shift['SID']) . " AND `TID`=" . sql_escape($angeltype['TID']) . " ORDER BY `Nick`");
					$entry_list = array ();
					foreach ($entries as $entry)
						$entry_list[] = $entry['Nick'];
					if ($angeltype['count'] - count($entries) > 0)
						if (!$my_shift || in_array('user_shift_admin', $privileges))
							$entry_list[] = '<a href="' . page_link_to('user_shifts') . '&shift_id=' . $shift['SID'] . '&type_id=' . $angeltype['TID'] . '">' . ($angeltype['count'] - count($entries)) . ' angel missing &raquo;</a>';
						else
							$entry_list[] = ($angeltype['count'] - count($entries)) . ' angel missing';
					$shifts_table .= join(", ", $entry_list);
					$shifts_table .= '<br />';
				}
			}
			$shifts_table .= '</td></tr>';
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
		if ($day == $d)
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
	return join(' | ', $html);
}
?>