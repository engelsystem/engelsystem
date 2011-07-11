<?php
function admin_import() {
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

			$html .= template_render('../templates/admin_import_check.html', array (
				'link' => page_link_to('admin_import'),
				'rooms_new' => count($rooms_new) == 0 ? "<td>None</td>" : table_body($rooms_new),
				'rooms_deleted' => count($rooms_deleted) == 0 ? "<td>None</td>" : table_body($rooms_deleted)
			));
			break;

		case "import" :
			list ($rooms_new, $rooms_deleted) = prepare_rooms();
			foreach ($rooms_new as $room)
				sql_query("INSERT INTO `Room` SET `Name`='" . sql_escape($room) . "', `FromPentabarf`='Y', `Show`='Y'");
			foreach ($rooms_deleted as $room)
				sql_query("DELETE FROM `Room` WHERE `Name`='" . sql_escape($room) . "' LIMIT 1");

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

	CreateRoomArrays();

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

	//readXMLfile("xml.php.xml");
	if (readXMLfile("../import/27C3_sample.xcs") == 0) {
		$XMLmain = getXMLsubPease($XMLmain, "VCALENDAR");

		if ($ShowDataStrukture) {
			$html .= "<pre><br />";
			$html .= $XMLmain->name;
			$html .= "<br />";
			print_r(array_values($XMLmain->sub));
			$html .= "</pre>";
		}

		/*
		$html .= "<br />";
		$Feld=7;
		$html .= "$Feld#". $XMLmain->sub[$Feld]->name. "<br />";
		$html .= "$Feld#". $XMLmain->sub[$Feld]->sub;
		//print_r(array_values ($XMLmain->sub[$Feld]->sub));
		while(list($key, $value) = each($XMLmain->sub[$Feld]->sub))
		  $html .= "?ID".$value->sub[1]->data. "=". $value->sub[2]->data. "\n";
		$html .= "</pre>";
		*/

		/*##############################################################################################
		                   V e r s i o n
		  ##############################################################################################*/

		$html .= "<hr>\n";
		$XMLrelease = getXMLsubPease($XMLmain, "X-WR-CALDESC");
		$html .= "release: " . $XMLrelease->data . "<br />\n";
		//$XMLreleaseDate = getXMLsubPease( $XMLmain, "RELEASE-DATE");
		//$html .= "release date: ". $XMLreleaseDate->data. "<br />\n";
		$html .= "<hr>\n";

		/*##############################################################################################
		                   V e r s i o n
		  ##############################################################################################*/
		if ($EnableRoomFunctions)
			include ("includes/funktion_xml_room.php");

		if ($EnableSchudleFunctions)
			include ("includes/funktion_xml_schudle.php");

		/*##############################################################################################
		                 U P D A T E  A L L 
		  ##############################################################################################*/
		$html .= "\n\n<br />\n<h1>Update ALL:</h1>\n";

		$html .= "<form action=\"dbUpdateFromXLS.php\">\n";
		$html .= "\t<input type=\"submit\" name=\"UpdateALL\" value=\"now\">\n";
		$html .= "</form>\n";

	} //if XMLopenOOK
	return $html;
}

/*##############################################################################################
        erstellt Arrays der Reume
  ##############################################################################################*/
function CreateRoomArrays() {
	global $Room, $RoomID, $RoomName, $con;

	$sql = "SELECT `RID`, `Name` FROM `Room` " .
	"WHERE `Show`='Y'" .
	"ORDER BY `Number`, `Name`;";
	$Erg = mysql_query($sql, $con);
	$rowcount = mysql_num_rows($Erg);

	for ($i = 0; $i < $rowcount; $i++) {
		$Room[$i]["RID"] = mysql_result($Erg, $i, "RID");
		$Room[$i]["Name"] = mysql_result($Erg, $i, "Name");
		$RoomID[mysql_result($Erg, $i, "RID")] = mysql_result($Erg, $i, "Name");
		$RoomName[mysql_result($Erg, $i, "Name")] = mysql_result($Erg, $i, "RID");
	}
}

function prepare_rooms() {
	$data = new SimpleXMLElement(file_get_contents('../import/27C3_sample.xcs'));

	// Load rooms from db for compare with input
	$rooms = sql_select("SELECT * FROM `Room` WHERE `FromPentabarf`='Y'");
	$rooms_db = array ();
	foreach ($rooms as $room)
		$rooms_db[] = $room['Name'];

	$events = $data->vcalendar->vevent;
	$rooms_pb = array ();
	foreach ($events as $event)
		$rooms_pb[] = $event->location;
	$rooms_pb = array_unique($rooms_pb);

	$rooms_new = array_diff($rooms_pb, $rooms_db);
	$rooms_deleted = array_diff($rooms_db, $rooms_pb);

	return array (
		$rooms_new,
		$rooms_deleted
	);
}
?>

