<?php
function admin_free() {
	global $privileges;

	$search = "";
	if (isset ($_REQUEST['search']))
		$search = strip_request_item('search');

	$users = sql_select("SELECT `User`.* FROM `User` LEFT JOIN `ShiftEntry` ON `User`.`UID` = `ShiftEntry`.`UID` LEFT JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID` AND `Shifts`.`start` < " . sql_escape(time()) . " AND `Shifts`.`end` > " . sql_escape(time()) . ") WHERE `User`.`Gekommen` = 1 AND `Shifts`.`SID` IS NULL GROUP BY `User`.`UID` ORDER BY `Nick`");

	$table = "";
	if ($search == "")
		$tokens = array ();
	else
		$tokens = explode(" ", $search);
	foreach ($users as $usr) {
		if (count($tokens) > 0) {
			$match = false;
			$index = join("", $usr);
			foreach ($tokens as $t)
				if (strstr($index, trim($t))) {
					$match = true;
					break;
				}
			if (!$match)
				continue;
		}
		$table .= '<tr>';
		if (in_array('user_shifts_admin', $privileges))
			$table .= '<td><a href="' . page_link_to('user_myshifts') . '&amp;id=' . $usr['UID'] . '">' . $usr['Nick'] . '</a></td>';
		else
			$table .= '<td>' . $usr['Nick'] . '</td>';
		$table .= '<td>' . $usr['DECT'] . '</td>';
		$table .= '<td>' . $usr['jabber'] . '</td>';
		if (in_array('admin_user', $privileges))
			$table .= '<td><a href="' . page_link_to('admin_user') . '&amp;id=' . $usr['UID'] . '">edit</a></td>';
		else
			$table .= '<td>' . $usr['Nick'] . '</td>';

		$table .= '</tr>';
	}
	return template_render('../templates/admin_free.html', array (
		'search' => $search,
		'table' => $table,
		'link' => page_link_to('admin_free')
	));
}
?>