<?php
function admin_angel_types() {
	$angel_types_source = sql_select("SELECT * FROM `AngelTypes` ORDER BY `name`");
	$angel_types = array ();
	foreach ($angel_types_source as $angel_type) {
		$angel_types[] = array (
			'id' => $angel_type['id'],
			'name' => $angel_type['name'],
			'restricted' => $angel_type['restricted'] == 1 ? '&#10003;' : '',
			'actions' => '<a class="action edit" href="' . page_link_to('admin_angel_types') . '&show=edit&id=' . $angel_type['id'] . '">edit</a> <a class="action delete" href="' . page_link_to('admin_angel_types') . '&show=delete&id=' . $angel_type['id'] . '">delete</a>'
		);
	}

	if (isset ($_REQUEST['show'])) {
		$msg = "";
		$name = "";
		$restricted = 0;

		if (test_request_int('id')) {
			$angel_type = sql_select("SELECT * FROM `AngelTypes` WHERE `id`=" . sql_escape($_REQUEST['id']));
			if (count($angel_type) > 0) {
				$id = $_REQUEST['id'];
				$name = $angel_type[0]['name'];
				$restricted = $angel_type[0]['restricted'];
			} else
				redirect(page_link_to('admin_angel_types'));
		}

		if ($_REQUEST['show'] == 'edit') {
			if (isset ($_REQUEST['submit'])) {
				$ok = true;

				if (isset ($_REQUEST['name']) && strlen(strip_request_item('name')) > 0) {
					$name = strip_request_item('name');
					if (sql_num_query("SELECT * FROM `AngelTypes` WHERE NOT `id`=" . sql_escape(isset ($id) ? $id : 0) . " AND `name`='" . sql_escape(strip_request_item('name')) . "' LIMIT 1") > 0) {
						$ok = false;
						$msg .= error("This angel type name is already given.", true);
					}
				} else {
					$ok = false;
					$msg .= error("Please enter a name.", true);
				}

				if (isset ($_REQUEST['restricted']))
					$restricted = 1;

				if ($ok) {
					if (isset ($id))
						sql_query("UPDATE `AngelTypes` SET `name`='" . sql_escape($name) . "', `restricted`=" . sql_escape($restricted) . " WHERE `id`=" . sql_escape($id) . " LIMIT 1");
					else
						sql_query("INSERT INTO `AngelTypes` SET `name`='" . sql_escape($name) . "', `restricted`=" . sql_escape($restricted));

					success("Angel type saved.");
					redirect(page_link_to('admin_angel_types'));
				}
			}

			return page(array (
				buttons(array (
					button(page_link_to('admin_angel_types'), "Back", 'back')
				)),
				$msg,
				form(array (
					form_text('name', 'Name', $name),
					form_checkbox('restricted', 'Restricted', $restricted),
					form_info("", "Restricted angel types can only be used by an angel if enabled by an archangel (double opt-in)."),
					form_submit('submit', 'Save')
				))
			));
		}
		elseif ($_REQUEST['show'] == 'delete') {
			if (isset ($_REQUEST['ack'])) {
				sql_query("DELETE FROM `NeededAngelTypes` WHERE `angel_type_id`=" . sql_escape($id) . " LIMIT 1");
				sql_query("DELETE FROM `ShiftEntry` WHERE `TID`=" . sql_escape($id) . " LIMIT 1");
				sql_query("DELETE FROM `AngelTypes` WHERE `id`=" . sql_escape($id) . " LIMIT 1");
				sql_query("DELETE FROM `UserAngelTypes` WHERE `angeltype_id`=" . sql_escape($id) . " LIMIT 1");
				success(sprintf("Angel type %s deleted.", $name));
				redirect(page_link_to('admin_angel_types'));
			}

			return page(array (
				buttons(array (
					button(page_link_to('admin_angel_types'), "Back", 'back')
				)),
				sprintf("Do you want to delete angel type %s?", $name),
				buttons(array (
					button(page_link_to('admin_angel_types') . '&show=delete&id=' . $id . '&ack', "Delete", 'delete')
				))
			));
		} else
			redirect(page_link_to('admin_angel_types'));
	}

	return page(array (
		buttons(array (
			button(page_link_to('admin_angel_types') . '&show=edit', "Add", 'add')
		)),
		msg(),
		table(array (
			'name' => "Name",
			'restricted' => "Restricted",
			'actions' => ""
		), $angel_types)
	));
}
?>
