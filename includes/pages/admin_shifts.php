<?php


// Assistent zum Anlegen mehrerer neuer Schichten
function admin_shifts() {
	$msg = "";
	$ok = true;

	// Name/Bezeichnung der Schicht, darf nicht leer sein
	if (isset ($_REQUEST['name']) && strlen($_REQUEST['name']) > 0)
		$name = strip_request_item('name');
	else {
		$ok = false;
		$name = "";
		$msg .= error("Gib bitte einen Namen für die Schicht(en) an.");
	}

	// Auswahl der sichtbaren Locations für die Schichten
	$rooms = sql_select("SELECT * FROM `Room` WHERE `show`='Y' ORDER BY `Name`");
	$room_array = array ();
	foreach ($rooms as $room)
		$room_array[$room['RID']] = $room['Name'];

	if (isset ($_REQUEST['rid']) && preg_match("/^[0-9]+$/") && isset ($room_array[$_REQUEST['rid']]))
		$rid = $_REQUEST['rid'];
	else {
		$ok = false;
		$rid = 0;
		$msg .= error("Wähle bitte einen Raum aus.");
	}
	
	

	$room_select = html_select_key('rid', $room_array, '');

	$types = sql_select("SELECT * FROM `AngelTypes` ORDER BY `Name`");
	$angel_types = "";
	foreach ($types as $type) {
		$angel_types .= template_render('../templates/admin_shifts_angel_types.html', array (
			'id' => $type['TID'],
			'type' => $type['Name'],
			'value' => "0"
		));
	}
	return template_render('../templates/admin_shifts.html', array (
		'angel_types' => $angel_types,
		'room_select' => $room_select
	));
}
?>