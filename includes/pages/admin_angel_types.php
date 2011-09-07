<?php

function admin_angel_types() {
	$html = "";
	if (!isset ($_REQUEST['action'])) {

		$table = "";
		$angel_types = sql_select("SELECT * FROM `AngelTypes` ORDER BY `Name`");

		foreach ($angel_types as $angel_type)
			$table .= sprintf(
				  '<tr><td>%s</td><td>%s</td><td>'
				. '<a href="%s&action=edit&id=%s">Edit</a></td></tr>',
				$angel_type['Name'], $angel_type['Man'],
				page_link_to("admin_angel_types"),
				$angel_type['TID']
			);

		$html .= template_render('../templates/admin_angel_types.html', array (
			'link' => page_link_to("admin_angel_types"),
			'table' => $table
		));

	} else {

		switch ($_REQUEST['action']) {

			case 'create' :
				$name = strip_request_item("name");
				$man = strip_request_item("man");

				sql_query("INSERT INTO `AngelTypes` SET `Name`='" . sql_escape($name) . "', `Man`='" . sql_escape($man) . "'");

				header("Location: " . page_link_to("admin_angel_types"));
				break;

			case 'edit' :
				if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
					$id = $_REQUEST['id'];
				else
					return error("Incomplete call, missing AngelType ID.");

				$angel_type = sql_select("SELECT * FROM `AngelTypes` WHERE `TID`=" . sql_escape($id) . " LIMIT 1");
				if (count($angel_type) > 0) {
					list ($angel_type) = $angel_type;

					$html .= template_render(
						'../templates/admin_angel_types_edit_form.html', array (
							'link' => page_link_to("admin_angel_types"),
							'id' => $id,
							'name' => $angel_type['Name'],
							'man' => $angel_type['Man']
					));
				} else
					return error("No Angel Type found.");
				break;

			case 'save' :
				if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
					$id = $_REQUEST['id'];
				else
					return error("Incomplete call, missing AngelType ID.");

				$angel_type = sql_select("SELECT * FROM `AngelTypes` WHERE `TID`=" . sql_escape($id) . " LIMIT 1");
				if (count($angel_type) > 0) {
					list ($angel_type) = $angel_type;

					$name = strip_request_item("name");
					$man = strip_request_item("man");

					sql_query("UPDATE `AngelTypes` SET `Name`='" . sql_escape($name) . "', `Man`='" . sql_escape($man) . "' WHERE `TID`=" . sql_escape($id) . " LIMIT 1");
					header("Location: " . page_link_to("admin_angel_types"));
				} else
					return error("No Angel Type found.");
				break;

			case 'delete' :
				if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
					$id = $_REQUEST['id'];
				else
					return error("Incomplete call, missing AngelType ID.");

				$angel_type = sql_select("SELECT * FROM `AngelTypes` WHERE `TID`=" . sql_escape($id) . " LIMIT 1");
				if (count($angel_type) > 0) {
					sql_query("DELETE FROM `AngelTypes` WHERE `TID`=" . sql_escape($id) . " LIMIT 1");
					sql_query("DELETE FROM `NeededAngelTypes` WHERE `angel_type_id`=" . sql_escape($id) . " LIMIT 1");
					header("Location: " . page_link_to("admin_angel_types"));
				} else
					return error("No Angel Type found.");
				break;
		}
	}

	return $html;
}
?>
