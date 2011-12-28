<?php


// Öffentlich zugängliche Funktion zum Abrufen von iCal-Exports der eigenen Schichten
function user_ical() {
	global $ical_shifts;

	if (isset ($_REQUEST['key']) && preg_match("/^[0-9a-f]{32}$/", $_REQUEST['key']))
		$key = $_REQUEST['key'];
	else
		die("Missing key.");

	$user = sql_select("SELECT * FROM `User` WHERE `ical_key`='" . sql_escape($key) . "' LIMIT 1");
	if (count($user) == 0)
		die("Key invalid.");

	$user = $user[0];

	if (isset ($_REQUEST['export']) && $_REQUEST['export'] == 'user_shifts') {
		require_once ('includes/pages/user_shifts.php');
		view_user_shifts();
	} else {
		$ical_shifts = sql_select("SELECT * FROM `ShiftEntry` JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`) JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `UID`=" . sql_escape($user['UID']) . " ORDER BY `start`");
	}

	header("Content-Type: text/calendar; charset=utf-8");
	echo "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//-//Engelsystem//DE\r\nCALSCALE:GREGORIAN\r\n";
	foreach ($ical_shifts as $shift) {
		echo "BEGIN:VEVENT\r\n";
		echo "UID:" . md5($shift['start'] . $shift['end'] . $shift['name']) . "\r\n";
		echo "SUMMARY:" . str_replace("\n", "\\n", preg_replace("/[^a-z]*/i", '', $shift['name'])) . "\r\n";
		echo "DESCRIPTION:" . str_replace("\n", "\\n", $shift['Comment']) . "\r\n";
		echo "DTSTART;TZID=Europe/Berlin:" . date("Ymd\THis", $shift['start']) . "\r\n";
		echo "DTEND;TZID=Europe/Berlin:" . date("Ymd\THis", $shift['end']) . "\r\n";
		echo "LOCATION:" . $shift['Name'] . "\r\n";
		echo "END:VEVENT\r\n";
	}
	echo "END:VCALENDAR\r\n";
	die();
}
?>
