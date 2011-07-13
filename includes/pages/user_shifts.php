<?php
function user_shifts() {
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
	if (isset ($_REQUEST['room_id']))
		$id = $_REQUEST['room_id'];

	$day_timestamp = DateTime :: createFromFormat("Y-m-d", $day)->getTimestamp();
	$shifts = sql_select("SELECT * FROM `Shifts` WHERE `RID`=" . sql_escape($id) . " AND `start` >= " . sql_escape($day_timestamp) . " AND `start` < " . sql_escape($day_timestamp +24 * 60 * 60) . " ORDER BY `start`");

	$shifts_table = "";
	foreach ($shifts as $shift) {
		$shifts_table .= '<tr><td>' . date("H:i", $shift['start']) . ' - ' . date("H:i", $shift['end']) . '</td></tr>';
	}

	return template_render('../templates/user_shifts.html', array (
		'room_select' => make_room_select($rooms, $id, $day),
		'day_select' => make_day_select($days, $day, $id),
		'shifts_table' => $shifts_table
	));
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