<?php


//
function user_myshifts() {
	global $LETZTES_AUSTRAGEN;
	global $user, $privileges;
	$msg = "";

	if (isset ($_REQUEST['edit']) && preg_match("/^[0-9]*$/", $_REQUEST['edit'])) {
		$id = $_REQUEST['edit'];
		$shift = sql_select("SELECT `ShiftEntry`.`Comment`, `Shifts`.*, `Room`.`Name`, `AngelTypes`.`Name` as `angel_type` FROM `ShiftEntry` JOIN `AngelTypes` ON (`ShiftEntry`.`TID` = `AngelTypes`.`TID`) JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`) JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `id`=" . sql_escape($id) . " AND `UID`=" . sql_escape($user['UID']) . " LIMIT 1");
		if (count($shift) > 0) {
			$shift = $shift[0];

			if (isset ($_REQUEST['submit'])) {
				$comment = strip_request_item_nl('comment');
				sql_query("UPDATE `ShiftEntry` SET `Comment`='" . sql_escape($comment) . "' WHERE `id`=" . sql_escape($id) . " LIMIT 1");
				header("Location: " . page_link_to('user_myshifts'));
			}

			return template_render('../templates/user_shifts_add.html', array (
				'angel' => $user['Nick'],
				'date' => date("Y-m-d H:i", $shift['start']) . ', ' . shift_length($shift),
				'location' => $shift['Name'],
				'title' => $shift['name'],
				'type' => $shift['angel_type'],
				'comment' => $shift['Comment']
			));
		} else
			header("Location: " . page_link_to('user_myshifts'));
	}
	elseif (isset ($_REQUEST['cancel']) && preg_match("/^[0-9]*$/", $_REQUEST['cancel'])) {
		$id = $_REQUEST['cancel'];
		$shift = sql_select("SELECT * FROM `ShiftEntry` WHERE `id`=" . sql_escape($id) . " AND `UID`=" . sql_escape($user['UID']) . " LIMIT 1");
		if (count($shift) > 0) {
			$shift = $shift[0];
			if (($shift['start'] - time() < $LETZTES_AUSTRAGEN * 60) || in_array('user_shifts_admin', $privileges)) {
				sql_query("DELETE FROM `ShiftEntry` WHERE `id`=" . sql_escape($id) . " LIMIT 1");
				$msg = success("Du wurdest aus der Schicht ausgetragen.");
			} else
				$msg = error("Es ist zu spät um sich aus der Schicht auszutragen. Frage ggf. einen Orga.'");
		} else
			header("Location: " . page_link_to('user_myshifts'));
	}
	$shifts = sql_select("SELECT * FROM `ShiftEntry` JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`) JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `UID`=" . sql_escape($user['UID']) . " ORDER BY `start`");
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
		$html .= '<td>';
		$html .= '<a href="' . page_link_to('user_myshifts') . '&edit=' . $shift['id'] . '">bearbeiten</a>';
		if ($shift['start'] - time() > $LETZTES_AUSTRAGEN * 60)
			$html .= ' | <a href="' . page_link_to('user_myshifts') . '&cancel=' . $shift['id'] . '">austragen</a>';
		$html .= '</td>';
		$html .= '</tr>';
	}
	if ($html == "")
		$html = '<tr><td>Keine...</td><td></td><td></td><td></td><td></td><td>Gehe zum <a href="' . page_link_to('user_shifts') . '">Schichtplan</a> um Dich für Schichten einzutragen.</td></tr>';

	return template_render('../templates/user_myshifts.html', array (
		'h' => $LETZTES_AUSTRAGEN,
		'shifts' => $html,
		'msg' => $msg
	));
}
?>