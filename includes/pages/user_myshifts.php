<?php


//
function user_myshifts() {
	global $LETZTES_AUSTRAGEN;
	global $user, $privileges;
	$msg = "";

	if (isset ($_REQUEST['cancel']) && preg_match("/^[0-9]*$/", $_REQUEST['cancel'])) {
		$id = $_REQUEST['cancel'];
		$shift = sql_select("SELECT * FROM `ShiftEntry` WHERE `id`=" . sql_escape($id) . " LIMIT 1");
		if (count($shift) > 0) {
			$shift = $shift[0];
			if (($shift['start'] - time() < $LETZTES_AUSTRAGEN * 60) || in_array('user_shifts_admin', $privileges)) {
				sql_query("DELETE FROM `ShiftEntry` WHERE `id`=" . sql_escape($id) . " LIMIT 1");
				$msg = success("Your shift has been canceled successfully.");
			} else
				$msg = error("It's too late to cancel this shift.'");
		} else
			header("Location: " . page_link_to('user_myshifts'));
	}
	$shifts = sql_select("SELECT * FROM `ShiftEntry` JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`) JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `UID`=" . sql_escape($user['UID'])." ORDER BY `start`");
	$html = "";
	foreach ($shifts as $shift) {
		if (time() > $shift['end'])
			$html .= '<tr class="done">';
		else
			$html .= '<tr>';
		$html .= '<td>' . date("Y-m-d", $shift['start']) . '</td>';
		$html .= '<td>' . date("H:i", $shift['start']) . ' - ' . date("H:i", $shift['end']) . '</td>';
		$html .= '<td>' . $shift['Name'] . '</td>';
		$html .= '<td>' . $shift['name'] . '</td>';
		$html .= '<td>' . $shift['Comment'] . '</td>';
		if ($shift['start'] - time() > $LETZTES_AUSTRAGEN * 60)
			$html .= '<td><a href="' . page_link_to('user_myshifts') . '&cancel=' . $shift['id'] . '">Cancel</a></td>';
		else
			$html .= '<td></td>';
		$html .= '</tr>';
	}
	if ($html == "")
		$html = '<tr><td>None...</td><td></td><td></td><td></td><td></td><td>Go to <a href="' . page_link_to('user_shifts') . '">Shifts</a> to sign up for a shift.</td></tr>';

	return template_render('../templates/user_myshifts.html', array (
		'h' => $LETZTES_AUSTRAGEN,
		'shifts' => $html,
		'msg' => $msg
	));
}
?>