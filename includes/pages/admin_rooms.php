<?php
function admin_rooms() {
	global $user;

	$rooms_source = sql_select("SELECT * FROM `Room` ORDER BY `Name`");
	$rooms = array ();
	foreach ($rooms_source as $room)
		$rooms[] = array (
			'name' => $room['Name'],
			'from_pentabarf' => $room['FromPentabarf'] == 'Y' ? '&#10003;' : '',
			'public' => $room['show'] == 'Y' ? '&#10003;' : '',
			'actions' => '<a class="ection edit" href="' . page_link_to('admin_rooms') . '&show=edit&id=' . $room['RID'] . '">edit</a> <a class="action delete" href="' . page_link_to('admin_rooms') . '&show=delete&id=' . $room['RID'] . '">delete</a>'
		);

	if (isset ($_REQUEST['show'])) {
		$msg = "";
		$name = "";
		$from_pentabarf = "";
		$public = 'Y';
		$number = "";

		$angeltypes_source = sql_select("SELECT * FROM `AngelTypes` ORDER BY `name`");
		$angeltypes = array ();
		$angeltypes_count = array ();
		foreach ($angeltypes_source as $angeltype) {
			$angeltypes[$angeltype['id']] = $angeltype['name'];
			$angeltypes_count[$angeltype['id']] = 0;
		}

		if (test_request_int('id')) {
			$room = sql_select("SELECT * FROM `Room` WHERE `RID`=" . sql_escape($_REQUEST['id']));
			if (count($room) > 0) {
				$id = $_REQUEST['id'];
				$name = $room[0]['Name'];
				$from_pentabarf = $room[0]['FromPentabarf'];
				$public = $room[0]['show'];
				$needed_angeltypes = sql_select("SELECT * FROM `NeededAngelTypes` WHERE `room_id`=" . sql_escape($id));
				foreach ($needed_angeltypes as $needed_angeltype)
					$angeltypes_count[$needed_angeltype['angel_type_id']] = $needed_angeltype['count'];
			} else
				redirect(page_link_to('admin_rooms'));
		}

		if ($_REQUEST['show'] == 'edit') {
			if (isset ($_REQUEST['submit'])) {
				$ok = true;

				if (isset ($_REQUEST['name']) && strlen(strip_request_item('name')) > 0)
					$name = strip_request_item('name');
				else {
					$ok = false;
					$msg .= error("Please enter a name.", true);
				}

				if (isset ($_REQUEST['from_pentabarf']))
					$from_pentabarf = 'Y';
				else
					$from_pentabarf = '';

				if (isset ($_REQUEST['public']))
					$public = 'Y';
				else
					$public = '';

				if (isset ($_REQUEST['number']))
					$number = strip_request_item('number');
				else
					$ok = false;

				foreach ($angeltypes as $angeltype_id => $angeltype)
					if (isset ($_REQUEST['angeltype_count_' . $angeltype_id]) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['angeltype_count_' . $angeltype_id]))
						$angeltypes_count[$angeltype_id] = $_REQUEST['angeltype_count_' . $angeltype_id];
					else {
						$ok = false;
						$msg .= error(sprintf("Please enter needed angels for type %s.", $angeltype), true);
					}

				if ($ok) {
					sql_query("UPDATE `Room` SET `Name`='" . sql_escape($name) . "', `FromPentabarf`='" . sql_escape($from_pentabarf) . "', `show`='" . sql_escape($public) . "', `Number`='" . sql_escape($number) . "' WHERE `RID`=" . sql_escape($id) . " LIMIT 1");
					sql_query("DELETE FROM `NeededAngelTypes` WHERE `room_id`=" . sql_escape($id));
					foreach ($angeltypes_count as $angeltype_id => $angeltype_count)
						sql_query("INSERT INTO `NeededAngelTypes` SET `room_id`=" . sql_escape($id) . ", `angel_type_id`=" . sql_escape($angeltype_id) . ", `count`=" . sql_escape($angeltype_count));

					success("Room saved.");
					redirect(page_link_to("admin_rooms"));
				}
			}
			$angeltypes_count_form = array ();
			foreach ($angeltypes as $angeltype_id => $angeltype)
				$angeltypes_count_form[] = form_text('angeltype_count_' . $angeltype_id, $angeltype, $angeltypes_count[$angeltype_id]);

			return page(array (
				buttons(array (
					button(page_link_to('admin_rooms'), "Back", 'back')
				)),
				$msg,
				form(array (
					form_text('name', "Name", $name),
					form_checkbox('from_pentabarf', "Pentabarf-Import", $from_pentabarf),
					form_checkbox('public', "Public", $public),
					form_text('number', "Number", $number),
					form_info("Needed angels:", ""),
					join($angeltypes_count_form),
					form_submit('submit', 'Save')
				))
			));
		}
		elseif ($_REQUEST['show'] == 'delete') {
			if (isset ($_REQUEST['ack'])) {
				sql_query("DELETE FROM `Room` WHERE `RID`=" . sql_escape($id) . " LIMIT 1");
				sql_query("DELETE FROM `NeededAngelTypes` WHERE `room_id`=" . sql_escape($id) . " LIMIT 1");
				success(sprintf("Room %s deleted.", $name));
				redirect(page_link_to('admin_rooms'));
			}

			return page(array (
				buttons(array (
					button(page_link_to('admin_rooms'), "Back", 'back')
				)),
				sprintf("Do you want to delete room %s?", $name),
				buttons(array (
					button(page_link_to('admin_rooms') . '&show=delete&id=' . $id . '&ack', "Delete", 'delete')
				))
			));
		}
	}

	return page(array (
		buttons(array (
			button(page_link_to('admin_rooms') . '&show=edit', "Add", 'add')
		)),
		msg(),
		table(array (
			'name' => "Name",
			'from_pentabarf' => "Pentabarf-Import",
			'public' => "Public",
			'actions' => ""
		), $rooms)
	));
}
?>
