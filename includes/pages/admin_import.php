<?php
function admin_import() {
	global $rooms_import;
	$html = "";

	$step = "input";
	if (isset ($_REQUEST['step']))
		$step = $_REQUEST['step'];

	$html .= '<p>';
	$html .= $step == "input" ? '<b>1. Input</b>' : '1. Input';
	$html .= ' &raquo; ';
	$html .= $step == "check" ? '<b>2. Validate</b>' : '2. Validate';
	$html .= ' &raquo; ';
	$html .= $step == "import" ? '<b>3. Import</b>' : '3. Import';
	$html .= '</p>';

	switch ($step) {
		case "input" :
			$ok = false;
			if (!$ok) {
				$html .= template_render('../templates/admin_import_input.html', array (
					'link' => page_link_to('admin_import')
				));
				break;
			}

		case "check" :
			list ($rooms_new, $rooms_deleted) = prepare_rooms();
			list ($events_new, $events_updated, $events_deleted) = prepare_events();

			$html .= template_render('../templates/admin_import_check.html', array (
				'link' => page_link_to('admin_import'),
				'rooms_new' => count($rooms_new) == 0 ? "<tr><td>None</td></tr>" : table_body($rooms_new),
				'rooms_deleted' => count($rooms_deleted) == 0 ? "<tr><td>None</td></tr>" : table_body($rooms_deleted),
				'events_new' => count($events_new) == 0 ? "<tr><td>None</td><td></td><td></td><td></td></tr>" : table_body(shifts_printable($events_new)),
				'events_updated' => count($events_updated) == 0 ? "<tr><td>None</td><td></td><td></td><td></td></tr>" : table_body(shifts_printable($events_updated)),
				'events_deleted' => count($events_deleted) == 0 ? "<tr><td>None</td><td></td><td></td><td></td></tr>" : table_body(shifts_printable($events_deleted))
			));
			break;

		case "import" :
			list ($rooms_new, $rooms_deleted) = prepare_rooms();
			foreach ($rooms_new as $room) {
				sql_query("INSERT INTO `Room` SET `Name`='" . sql_escape($room) . "', `FromPentabarf`='Y', `Show`='Y'");
				$rooms_import[trim($room)] = sql_id();
			}
			foreach ($rooms_deleted as $room)
				sql_query("DELETE FROM `Room` WHERE `Name`='" . sql_escape($room) . "' LIMIT 1");

			list ($events_new, $events_updated, $events_deleted) = prepare_events();
			foreach ($events_new as $event)
				sql_query("INSERT INTO `Shifts` SET `start`=" . sql_escape($event['start']) . ", `end`=" . sql_escape($event['end']) . ", `RID`=" . sql_escape($event['RID']) . ", `PSID`=" . sql_escape($event['PSID']) . ", `URL`='" . sql_escape($event['URL']) . "'");

			foreach ($events_updated as $event)
				sql_query("UPDATE `Shifts` SET `start`=" . sql_escape($event['start']) . ", `end`=" . sql_escape($event['end']) . ", `RID`=" . sql_escape($event['RID']) . ", `PSID`=" . sql_escape($event['PSID']) . ", `URL`='" . sql_escape($event['URL']) . "' WHERE `PSID`=" . sql_escape($event['PSID']) . " LIMIT 1");

			foreach ($events_deleted as $event)
				sql_query("DELETE FROM `Shifts` WHERE `PSID`=" .
				sql_escape($event['PSID']) . " LIMIT 1");

			$html .= template_render('../templates/admin_import_import.html', array ());
			break;
	}

	return $html;

	##############################################################################################
	global $Room, $RoomID, $RoomName;
	global $PentabarfGetWith, $PentabarfXMLpath, $PentabarfXMLhost;

	require_once ("includes/funktion_xml.php");
	///////////
	// DEBUG //
	///////////
	$ShowDataStrukture = 0;
	$EnableRoomFunctions = 1;
	$EnableRooms = 1;
	$EnableSchudleFunctions = 1;
	$EnableSchudle = 1;
	$EnableSchudleDB = 1;

	$html = "";

	/*##############################################################################################
	                   F I L E
	  ##############################################################################################*/
	$html .= "\n\n<br />\n<h1>XML File:</h1>\n";
	if (isset ($_POST["PentabarfUser"]) && isset ($_POST["password"]) && isset ($_POST["PentabarfURL"])) {
		$html .= "Update XCAL-File from Pentabarf..";
		if ($PentabarfGetWith == "fsockopen") {

			//backup error messeges and delate
			$Backuperror_messages = $error_messages;
			$fp = fsockopen("ssl://$PentabarfXMLhost", 443, $errno, $errstr, 30);
			//  $error_messages = $Backuperror_messages;

			if (!$fp) {
				$html .= "<h2>fail: File 'https://$PentabarfXMLhost/$PentabarfXMLpath" . $_POST["PentabarfURL"] . "' not readable!" .
				"[$errstr ($errno)]</h2>";
			} else {
				if (($fileOut = fopen("$Tempdir/engelXML", "w")) != FALSE) {
					$head = 'GET /' . $PentabarfXMLpath . $_POST["PentabarfURL"] . ' HTTP/1.1' . "\r\n" .
					'Host: ' . $PentabarfXMLhost . "\r\n" .
					'User-Agent: Engelsystem' . "\r\n" .
					'Authorization: Basic ' .
					base64_encode($_POST["PentabarfUser"] . ':' . $_POST["password"]) . "\r\n" .
					"\r\n";
					fputs($fp, $head);
					$Zeilen = -1;
					while (!feof($fp)) {
						$Temp = fgets($fp, 1024);

						// ende des headers
						if ($Temp == "f20\r\n") {
							$Zeilen = 0;
							$Temp = "";
						}

						//file ende?
						if ($Temp == "0\r\n")
							break;

						if (($Zeilen > -1) && ($Temp != "ffb\r\n")) {
							//steuerzeichen ausfiltern
							if (strpos("#$Temp", "\r\n") > 0)
								$Temp = substr($Temp, 0, strlen($Temp) - 2);
							if (strpos("#$Temp", "1005") > 0)
								$Temp = "";
							if (strpos("#$Temp", "783") > 0)
								$Temp = "";
							//schreiben in file
							fputs($fileOut, $Temp);
							$Zeilen++;
						}
					}
					fclose($fileOut);

					$html .= "<br />Es wurden $Zeilen Zeilen eingelesen<br />";
				} else
					$html .= "<h2>fail: File '$Tempdir/engelXML' not writeable!</h2>";
				fclose($fp);
			}
		}
		elseif ($PentabarfGetWith == "fopen") {
			//user uns password in url einbauen
			$FileNameIn = "https://" . $_POST["PentabarfUser"] . ':' . $_POST["password"] . "@" .
			$PentabarfXMLhost . "/" . $PentabarfXMLpath . $_POST["PentabarfURL"];

			if (($fileIn = fopen($FileNameIn, "r")) != FALSE) {
				if (($fileOut = fopen("$Tempdir/engelXML", "w")) != FALSE) {
					$Zeilen = 0;
					while (!feof($fileIn)) {
						$Zeilen++;
						fputs($fileOut, fgets($fileIn));
					}
					fclose($fileOut);
					$html .= "<br />Es wurden $Zeilen Zeilen eingelesen<br />";
				} else
					$html .= "<h2>fail: File '$Tempdir/engelXML' not writeable!</h2>";
				fclose($fileIn);
			} else
				$html .= "<h2>fail: File 'https://$PentabarfXMLhost/$PentabarfXMLpath" . $_POST["PentabarfURL"] . "' not readable!</h2>";
		}
		elseif ($PentabarfGetWith == "wget") {
			$Command = "wget --http-user=" . $_POST["PentabarfUser"] . " --http-passwd=" . $_POST["password"] . " " .
			"https://$PentabarfXMLhost/$PentabarfXMLpath" . $_POST["PentabarfURL"] .
			" --output-file=$Tempdir/engelXMLwgetLog --output-document=$Tempdir/engelXML" .
			" --no-check-certificate";
			$html .= system($Command, $Status);
			if ($Status == 0)
				$html .= "OK.<br />";
			else
				$html .= "fail ($Status)($Command).<br />";
		}
		elseif ($PentabarfGetWith == "lynx") {
			$Command = "lynx -auth=" . $_POST["PentabarfUser"] . ":" . $_POST["password"] . " -dump " .
			"https://$PentabarfXMLhost/$PentabarfXMLpath" . $_POST["PentabarfURL"] . " > $Tempdir/engelXML";
			$html .= system($Command, $Status);
			if ($Status == 0)
				$html .= "OK.<br />";
			else
				$html .= "fail ($Status)($Command).<br />";
		}
		elseif ($PentabarfGetWith == "fopen") {
			//user uns password in url einbauen
			$FileNameIn = "https://" . $_POST["PentabarfUser"] . ':' . $_POST["password"] . "@" .
			$PentabarfXMLhost . "/" . $PentabarfXMLpath . $_POST["PentabarfURL"];

			if (($fileIn = fopen($FileNameIn, "r")) != FALSE) {
				if (($fileOut = fopen("$Tempdir/engelXML", "w")) != FALSE) {
					$Zeilen = 0;
					while (!feof($fileIn)) {
						$Zeilen++;
						fputs($fileOut, fgets($fileIn));
					}
					fclose($fileOut);
					$html .= "<br />Es wurden $Zeilen Zeilen eingelesen<br />";
				} else
					$html .= "<h2>fail: File '$Tempdir/engelXML' not writeable!</h2>";
				fclose($fileIn);
			} else
				$html .= "<h2>fail: File 'https://$PentabarfXMLhost/$PentabarfXMLpath" . $_POST["PentabarfURL"] . "' not readable!</h2>";
		}
		elseif ($PentabarfGetWith == "wget") {
			$Command = "wget --http-user=" . $_POST["PentabarfUser"] . " --http-passwd=" . $_POST["password"] . " " .
			"https://$PentabarfXMLhost/$PentabarfXMLpath" . $_POST["PentabarfURL"] .
			" --output-file=$Tempdir/engelXMLwgetLog --output-document=$Tempdir/engelXML" .
			" --no-check-certificate";
			$html .= system($Command, $Status);
			if ($Status == 0)
				$html .= "OK.<br />";
			else
				$html .= "fail ($Status)($Command).<br />";
		}
		elseif ($PentabarfGetWith == "lynx") {
			$Command = "lynx -auth=" . $_POST["PentabarfUser"] . ":" . $_POST["password"] . " -dump " .
			"https://$PentabarfXMLhost/$PentabarfXMLpath" . $_POST["PentabarfURL"] . " > $Tempdir/engelXML";
			$html .= system($Command, $Status);
			if ($Status == 0)
				$html .= "OK.<br />";
			else
				$html .= "fail ($Status)($Command).<br />";
		} else
			$html .= "<h1>The PentabarfGetWith='$PentabarfGetWith' not supported</h1>";
	} else {
		$html .= "<form action=\"dbUpdateFromXLS.php\" method=\"post\">\n";
		$html .= "<table border=\"0\">\n";
		$html .= "\t<tr><td>XCAL-File: https://$PentabarfXMLhost/$PentabarfXMLpath</td>" .
		"<td><input name=\"PentabarfURL\" type=\"text\" size=\"4\" maxlength=\"5\" " .
		"value=\"$PentabarfXMLEventID\"></td></tr>\n";
		$html .= "\t<tr><td>Username:</td>" .
		"<td><input name=\"PentabarfUser\" type=\"text\" size=\"30\" maxlength=\"30\"></td></tr>\n";
		$html .= "\t<tr><td>Password:</td>" .
		"<td><input name=\"password\" type=\"password\" size=\"30\" maxlength=\"30\"></td></tr>\n";
		$html .= "\t<tr><td></td><td><input type=\"submit\" name=\"FileUpload\" value=\"upload\"></td></tr>\n";
		$html .= "</table>\n";
		$html .= "</form>\n";
	}

	return $html;
}

function prepare_rooms() {
	global $rooms_import;
	$data = read_xml();

	// Load rooms from db for compare with input
	$rooms = sql_select("SELECT * FROM `Room` WHERE `FromPentabarf`='Y'");
	$rooms_db = array ();
	$rooms_import = array ();
	foreach ($rooms as $room) {
		$rooms_db[] = $room['Name'];
		$rooms_import[$room['Name']] = $room['RID'];
	}

	$events = $data->vcalendar->vevent;
	$rooms_pb = array ();
	foreach ($events as $event) {
		$rooms_pb[] = $event->location;
		if (!isset ($rooms_import[trim($event->location)]))
			$rooms_import[trim($event->location)] = trim($event->location);
	}
	$rooms_pb = array_unique($rooms_pb);

	$rooms_new = array_diff($rooms_pb, $rooms_db);
	$rooms_deleted = array_diff($rooms_db, $rooms_pb);

	return array (
		$rooms_new,
		$rooms_deleted
	);
}

function prepare_events() {
	global $rooms_import;
	$data = read_xml();

	$rooms = sql_select("SELECT * FROM `Room`");
	$rooms_db = array ();
	foreach ($rooms as $room)
		$rooms_db[$room['Name']] = $room['RID'];

	$events = $data->vcalendar->vevent;
	$shifts_pb = array ();
	foreach ($events as $event) {
		$event_pb = $event->children("http://pentabarf.org");
		$event_id = trim($event_pb-> {
			'event-id' });
		$shifts_pb[$event_id] = array (
			'start' => DateTime :: createFromFormat("Ymd\THis", $event->dtstart)->getTimestamp(),
			'end' => DateTime :: createFromFormat("Ymd\THis", $event->dtend)->getTimestamp(),
			'RID' => $rooms_import[trim($event->location)],
			'URL' => trim($event->url),
			'PSID' => $event_id
		);
	}

	$shifts = sql_select("SELECT * FROM `Shifts` WHERE `PSID` IS NOT NULL ORDER BY `start`");
	$shifts_db = array ();
	foreach ($shifts as $shift)
		$shifts_db[$shift['PSID']] = $shift;

	$shifts_new = array ();
	$shifts_updated = array ();
	foreach ($shifts_pb as $shift)
		if (!isset ($shifts_db[$shift['PSID']]))
			$shifts_new[] = $shift;
		else {
			$tmp = $shifts_db[$shift['PSID']];
			if ($shift['start'] != $tmp['start'] || $shift['end'] != $tmp['end'] || $shift['RID'] != $tmp['RID'] || $shift['URL'] != $tmp['URL'])
				$shifts_updated[] = $shift;
		}

	$shifts_deleted = array();
	foreach ($shifts_db as $shift)
		if (!isset ($shifts_pb[$shift['PSID']]))
			$shifts_deleted[] = $shift;

	return array (
		$shifts_new,
		$shifts_updated,
		$shifts_deleted
	);
}

function read_xml() {
	global $xml_import;
	if (!isset ($xml_import))
		$xml_import = new SimpleXMLElement(file_get_contents('../import/27C3_sample.xcs'));
	return $xml_import;
}

function shifts_printable($shifts) {
	global $rooms_import;
	$rooms = array_flip($rooms_import);

	uasort($shifts, 'shift_sort');

	$shifts_printable = array ();
	foreach ($shifts as $shift)
		$shifts_printable[] = array (
			'day' => date("l, Y-m-d", $shift['start']),
			'start' => date("H:i", $shift['start']),
			'end' => date("H:i", $shift['end']),
			'room' => $rooms[$shift['RID']]
		);
	return $shifts_printable;
}

function shift_sort($a, $b) {
	return ($a['start'] < $b['start']) ? -1 : 1;
}
?>

