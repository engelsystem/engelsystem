<?php


// Zeigt die Schichten an, die ein Benutzer belegt
function user_myshifts() {
	global $LETZTES_AUSTRAGEN;
	global $user, $privileges;
	$msg = "";

	if (isset ($_REQUEST['id']) && in_array("user_shifts_admin", $privileges) && preg_match("/^[0-9]{1,}$/", $_REQUEST['id']) && sql_num_query("SELECT * FROM `User` WHERE `UID`=" . sql_escape($_REQUEST['id'])) > 0) {
		$id = $_REQUEST['id'];
	} else {
		$id = $user['UID'];
	}

	list ($shifts_user) = sql_select("SELECT * FROM `User` WHERE `UID`=" . sql_escape($id) . " LIMIT 1");

	if ($id != $user['UID'])
		$msg .= info(sprintf("You are viewing %s's shifts.", $shifts_user['Nick']), true);

	if (isset ($_REQUEST['reset'])) {
		if ($_REQUEST['reset'] == "ack") {
			user_reset_ical_key($user);
			success("Key geändert.");
			redirect(page_link_to('user_myshifts'));
		}
		return template_render('../templates/user_myshifts_reset.html', array ());
	}
	elseif (isset ($_REQUEST['edit']) && preg_match("/^[0-9]*$/", $_REQUEST['edit'])) {
		$id = $_REQUEST['edit'];
		$shift = sql_select("SELECT `ShiftEntry`.`Comment`, `Shifts`.*, `Room`.`Name`, `AngelTypes`.`name` as `angel_type` FROM `ShiftEntry` JOIN `AngelTypes` ON (`ShiftEntry`.`TID` = `AngelTypes`.`id`) JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`) JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `ShiftEntry`.`id`=" . sql_escape($id) . " AND `UID`=" . sql_escape($shifts_user['UID']) . " LIMIT 1");
		if (count($shift) > 0) {
			$shift = $shift[0];

			if (isset ($_REQUEST['submit'])) {
				$comment = strip_request_item_nl('comment');
				sql_query("UPDATE `ShiftEntry` SET `Comment`='" . sql_escape($comment) . "' WHERE `id`=" . sql_escape($id) . " LIMIT 1");

				success("Schicht gespeichert.");
				redirect(page_link_to('user_myshifts'));
			}

			return template_render('../templates/user_shifts_add.html', array (
				'angel' => $shifts_user['Nick'],
				'date' => date("Y-m-d H:i", $shift['start']) . ', ' . shift_length($shift),
				'location' => $shift['Name'],
				'title' => $shift['name'],
				'type' => $shift['angel_type'],
				'comment' => $shift['Comment']
			));
		} else
			redirect(page_link_to('user_myshifts'));
	}
	elseif (isset ($_REQUEST['cancel']) && preg_match("/^[0-9]*$/", $_REQUEST['cancel'])) {
		$id = $_REQUEST['cancel'];
		$shift = sql_select("SELECT * FROM `ShiftEntry` WHERE `id`=" . sql_escape($id) . " AND `UID`=" . sql_escape($shifts_user['UID']) . " LIMIT 1");
		if (count($shift) > 0) {
			$shift = $shift[0];
			if (($shift['start'] - time() < $LETZTES_AUSTRAGEN * 60) || in_array('user_shifts_admin', $privileges)) {
				sql_query("DELETE FROM `ShiftEntry` WHERE `id`=" . sql_escape($id) . " LIMIT 1");
				$msg .= success(Get_Text("pub_myshifts_signed_off"), true);
			} else
				$msg .= error(Get_Text("pub_myshifts_too_late"), true);
		} else
			redirect(page_link_to('user_myshifts'));
	}
	$shifts = sql_select("SELECT * FROM `ShiftEntry` JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`) JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `UID`=" . sql_escape($shifts_user['UID']) . " ORDER BY `start`");

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
		$html .= '<a href="' . page_link_to('user_myshifts') . '&edit=' . $shift['id'] . '">' . Get_Text('edit') . '</a>';
		if ($shift['start'] - time() > $LETZTES_AUSTRAGEN * 60)
			$html .= ' | <a href="' . page_link_to('user_myshifts') . '&cancel=' . $shift['id'] . '">' . Get_Text('sign_off') . '</a>';
		$html .= '</td>';
		$html .= '</tr>';
	}
	if ($html == "")
		$html = '<tr><td>' . ucfirst(Get_Text('none')) . '...</td><td></td><td></td><td></td><td></td><td>' . sprintf(Get_Text('pub_myshifts_goto_shifts'), page_link_to('user_shifts')) . '</td></tr>';

	if ($shifts_user['ical_key'] == "")
		user_reset_ical_key($shifts_user);

	return msg().template_render('../templates/user_myshifts.html', array (
		'intro' => sprintf(Get_Text('pub_myshifts_intro'), $LETZTES_AUSTRAGEN),
		'shifts' => $html,
		'msg' => $msg,
		'ical_text' => sprintf(Get_Text('pub_schichtplan_ical_text'),
			page_link_to_absolute('ical') . '&key=' . $shifts_user['ical_key'],
			page_link_to('user_myshifts') . '&reset'),
));
}
?>
