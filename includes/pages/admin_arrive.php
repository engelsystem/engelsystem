<?php
function admin_arrive() {
	$msg = "";

	if (isset ($_REQUEST['reset']) && preg_match("/^[0-9]*$/", $_REQUEST['reset'])) {
		$id = $_REQUEST['reset'];
		sql_query("UPDATE `User` SET `Gekommen`=0 WHERE `UID`=" . sql_escape($id) . " LIMIT 1");
		$msg = success("Reset done. Angel has not arrived.");
	}
	elseif (isset ($_REQUEST['arrived']) && preg_match("/^[0-9]*$/", $_REQUEST['arrived'])) {
		$id = $_REQUEST['arrived'];
		sql_query("UPDATE `User` SET `Gekommen`=1 WHERE `UID`=" . sql_escape($id) . " LIMIT 1");
		$msg = success("Angel has been marked as arrived.");
	}

	$users = sql_select("SELECT * FROM `User` ORDER BY `Nick`");
	$table = "";
	foreach ($users as $usr) {
		$table .= '<tr>';
		$table .= '<td>' . $usr['Nick'] . '</td>';
		if ($usr['Gekommen'] == 1)
			$table .= '<td>yes</td><td><a href="' . page_link_to('admin_arrive') . '&reset=' . $usr['UID'] . '">reset</a></td>';
		else
			$table .= '<td></td><td><a href="' . page_link_to('admin_arrive') . '&arrived=' . $usr['UID'] . '">arrived</a></td>';
		$table .= '</tr>';
	}
	return template_render('../templates/admin_arrive.html', array (
		'search' => "",
		'table' => $table,
		'msg' => $msg
	));
}
?>