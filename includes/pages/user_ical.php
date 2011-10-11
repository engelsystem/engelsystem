<?php


// Öffentlich zugängliche Funktion zum Abrufen von iCal-Exports der eigenen Schichten
function user_ical() {
	if (isset ($_REQUEST['key']) && preg_match("/^[0-9a-f]{32}$/", $_REQUEST['key']))
		$key = $_REQUEST['key'];
	else
		die("Missing key.");

	$user = sql_select("SELECT * FROM `User` WHERE `ical_key`='" . sql_escape($key) . "' LIMIT 1");
	if (count($user) == 0)
		die("Key invalid.");

	$user = $user[0];

	$shifts = sql_select("SELECT * FROM `ShiftEntry` JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`) JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) WHERE `UID`=" . sql_escape($user['UID']) . " ORDER BY `start`");

	header("Content-Type: text/calendar");
	echo "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//-//Engelsystem//DE\nCALSCALE:GREGORIAN\n";
	foreach ($shifts as $shift) {
		echo "BEGIN:VEVENT\n";
		echo "UID:" . md5($shift['start'] . $shift['end'] . $shift['name']) . "\n";
		echo "SUMMARY:" . str_replace("\n", "\\n", $shift['name']) . "\n";
		echo "DESCRIPTION:" . str_replace("\n", "\\n", $shift['Comment']) . "\n";
		echo "DTSTART;TZID=Europe/Berlin:" . date("Ymd\THis", $shift['start']) . "\n";
		echo "DTEND;TZID=Europe/Berlin:" . date("Ymd\THis", $shift['end']) . "\n";
		echo "LOCATION:" . $shift['Name'] . "\n";
		echo "END:VEVENT\n";
	}
	echo "END:VCALENDAR\n";
	die();
}
?>
