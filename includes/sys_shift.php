<?php
function load_shift_basics() {
	global $VeranstaltungsTageMax, $Room, $RoomID, $EngelType, $EngelTypeID, $TID2Name, $Veranstaltungstage;

	// erstellt ein Array der Räume
	$rooms = sql_select("SELECT `RID`, `Name` FROM `Room` WHERE `Show`='Y' ORDER BY `Number`, `Name`");
	foreach ($rooms as $i => $r) {
		$Room[$i] = array (
			'RID' => $r['RID'],
			'Name' => $r['Name']
		);
		$RoomID[$r['RID']] = $r['Name'];
	}

	// erstellt ein Array der Engeltypen
	$engel_types = sql_select("SELECT `TID`, `Name` FROM `EngelType` ORDER BY `Name`");
	foreach ($engel_types as $engel_type) {
		$EngelType[$i] = array (
			'TID' => $engel_type['TID'],
			'Name' => $engel_type['Name'] . Get_Text("inc_schicht_engel")
		);
		$EngelTypeID[$engel_type['TID']] = $engel_type['Name'] . Get_Text("inc_schicht_engel");
		$TID2Name[$engel_type['TID']] = $engel_type['Name'];
	}

	// Erste Schicht suchen
	$Pos = 0;
	$first_shift = sql_select("SELECT `DateS` FROM `Shifts` ORDER BY `DateS` LIMIT 1");
	if (count($first_shift) > 0) {
		do {
			// Startdatum einlesen und link ausgeben
			$DateS = substr($first_shift[0]['DateS'], 0, 10);
			$VeranstaltungsTage[$Pos++] = $DateS;

			// auslesen den endes und eventuelle weitere tage ausgeben
			$last_shift = sql_select("SELECT MAX(`DateE`) FROM `Shifts` WHERE ( (`DateS` like '" . sql_escape($DateS) . "%') AND NOT (`DateE` like '%00:00:00'))");
			$DateE = substr($last_shift[0]['DateE'], 0, 10);

			if (strlen($DateE) == 0)
				$DateE = $DateS;
			else
				while ($DateS != $DateE) {
					$DateS = DatumUm1TagErhoehen($DateS);
					$VeranstaltungsTage[$Pos++] = $DateS;
				}

			// suchen den nächsten eintrag
			$first_shift = sql_select("SELECT `DateS` FROM `Shifts` " . "WHERE (`DateS` > '" . sql_escape($DateE) . " 23:59:59' ) " . "ORDER BY `DateS` " . "LIMIT 1");
		} while (count($first_shift) > 0);
	}

	$VeranstaltungsTageMax = $Pos -1;
}

/*#######################################################
#  gibt die engelschischten aus      #
#######################################################*/
function ausgabe_Feld_Inhalt($SID, $Man) {
	// gibt, nach �bergabe der der SchichtID (SID) und der RaumBeschreibung,
	// die eingetragenden und und offenden Schichteint�ge zur�ck
	global $EngelType, $EngelTypeID, $TID2Name, $con, $debug, $gmdateOffset;

	$Spalten = "";

	if (!isset ($_GET["Icon"]))
		$_GET["Icon"] = 1;

	///////////////////////////////////////////////////////////////////
	// Schow Admin Page
	///////////////////////////////////////////////////////////////////
	$Spalten .= funktion_isLinkAllowed_addLink_OrEmpty("admin/schichtplan.php?action=change&SID=$SID", "edit<br />\n");

	///////////////////////////////////////////////////////////////////
	// Ausgabe des Schischtnamens
	///////////////////////////////////////////////////////////////////
	$SQL = "SELECT `URL` FROM `Shifts` WHERE (`SID` = '$SID');";
	$Erg = mysql_query($SQL, $con);
	if (mysql_result($Erg, 0, 0) != "")
		$Spalten .= "<a href=\"" . mysql_result($Erg, 0, 0) . "\" target=\"_black\"><u>$Man:</u></a><br />";
	else
		$Spalten .= "<u>" .
		$Man . ":</u><br />";

	///////////////////////////////////////////////////////////////////
	// SQL abfrage f�r die ben�tigten schichten
	///////////////////////////////////////////////////////////////////
	$SQL = "SELECT * FROM `ShiftEntry` WHERE (`SID` = '$SID') ORDER BY `TID`, `UID` DESC ;";
	$Erg = mysql_query($SQL, $con);

	$Anzahl = mysql_num_rows($Erg);
	$Feld = 0;
	$Temp_TID_old = -1;
	for ($i = 0; $i < $Anzahl; $i++) {
		if (isset ($Temp[$Feld]["TID"]))
			$Temp_TID_old = $Temp[$Feld]["TID"];
		if (isset ($Temp[$Feld]["UID"]))
			$Temp_UID_old = $Temp[$Feld]["UID"];

		$Temp_TID = mysql_result($Erg, $i, "TID");

		// wenn sich der Type �ndert wird zumn�sten feld geweckselt
		if ($Temp_TID_old != $Temp_TID)
			$Feld++;

		$Temp[$Feld]["TID"] = $Temp_TID;
		$Temp[$Feld]["UID"] = mysql_result($Erg, $i, "UID");

		// sonderfall ersten durchlauf
		if ($i == 0) {
			$Temp_TID_old = $Temp[$Feld]["TID"];
			$Temp_UID_old = $Temp[$Feld]["UID"];
		}

		// ist es eine zu vergeben schicht?
		if ($Temp[$Feld]["UID"] == 0) {
			if (isset ($Temp[$Feld]["free"]))
				$Temp[$Feld]["free"]++;
			else
				$Temp[$Feld]["free"] = 1;
		} else
			$Temp[$Feld]["Engel"][] = $Temp[$Feld]["UID"];
	} // FOR

	///////////////////////////////////////////////////////////////////
	// Aus gabe der Schicht
	///////////////////////////////////////////////////////////////////
	if (isset ($Temp))
		if (count($Temp))
			foreach ($Temp as $TempEntry => $TempValue) {
				if (!isset ($TempValue["free"]))
					$TempValue["free"] = 0;

				// ausgabe EngelType
				$Spalten .= $EngelTypeID[$TempValue["TID"]] . " ";

				// ausgabe Eingetragener Engel
				if (isset ($TempValue["Engel"]))
					if (count($TempValue["Engel"]) > 0) {
						if (count($TempValue["Engel"]) == 1)
							$Spalten .= Get_Text("inc_schicht_ist") . ":<br />\n";
						else
							$Spalten .= Get_Text("inc_schicht_sind") . ":<br />\n";

						foreach ($TempValue["Engel"] as $TempEngelEntry => $TempEngelID) {
							if (funktion_isLinkAllowed("admin/user.php") === TRUE) {
								// add color, wenn Engel "Gekommen"
								$TempText = ((UIDgekommen($TempEngelID) == "1") ? "<span style=\"color: blue;\">" : "<span style=\"color: red;\">") .
								UID2Nick($TempEngelID) . "</span>";
							} else {
								$TempText = UID2Nick($TempEngelID);
							}

							// add link to user
							$TempText = funktion_isLinkAllowed_addLink_OrLinkText("admin/userChangeNormal.php?enterUID=$TempEngelID&Type=Normal", $TempText);

							$Spalten .= "&nbsp;&nbsp;" . $TempText .
							 (($_GET["Icon"] == 1) ? DisplayAvatar($TempEngelID) : "") .
							"<br />\n";

						}
						$Spalten = substr($Spalten, 0, strlen($Spalten) - 7);
					}

				// ausgabe ben�tigter Engel
				////////////////////////////
				//in vergangenheit
				$SQLtime = "SELECT `DateE` FROM `Shifts` WHERE (`SID`='$SID' AND `DateE` >= '" .
				gmdate("Y-m-d H:i:s", time() + $gmdateOffset) . "')";
				$Ergtime = mysql_query($SQLtime, $con);
				if (mysql_num_rows($Ergtime) > 0) {
					//wenn keien rechte definiert sind 
					if (!isset ($_SESSION['CVS'][$TID2Name[$TempValue["TID"]]]))
						$_SESSION['CVS'][$TID2Name[$TempValue["TID"]]] = "Y";

					if ($_SESSION['CVS'][$TID2Name[$TempValue["TID"]]] == "Y")
						if ($TempValue["free"] > 0) {
							$Spalten .= "<br />\n&nbsp;&nbsp;<a href=\"./schichtplan_add.php?SID=$SID&TID=" .
							$TempValue["TID"] . "\">";
							$Spalten .= $TempValue["free"];
							if ($TempValue["free"] != 1)
								$Spalten .= Get_Text("inc_schicht_weitere") .
								" " . Get_Text("inc_schicht_Engel") .
								Get_Text("inc_schicht_wird");
							else
								$Spalten .= Get_Text("inc_schicht_weiterer") .
								" " . Get_Text("inc_schicht_Engel") .
								Get_Text("inc_schicht_werden");
							$Spalten .= Get_Text("inc_schicht_noch_gesucht");
							$Spalten .= "</a>";
						}
				} else {
					if (isset ($TempValue["free"]))
						if ($TempValue["free"] > 0)
							$Spalten .= "<br />\n&nbsp;&nbsp;<h3><a>Fehlen noch: " .
							$TempValue["free"] . "</a></h3>";
				}
				$Spalten .= "<br />\n";

			} // FOREACH
	return $Spalten;
} // function Ausgabe_Feld_Inhalt

/*#######################################################
#  gibt die engelschischten Druckergerecht aus  #
#######################################################*/
function Ausgabe_Feld_Inhalt_Druck($RID, $Man) {
	// gibt, nach �bergabe der der SchichtID (SID) und der RaumBeschreibung,
	// die eingetragenden und und offenden Schichteint�ge zur�ck

} // function Ausgabe_Feld_Inhalt

/*#######################################################
#  Ausgabe der Raum Spalten      #
#######################################################*/
function CreateRoomShifts($raum) {
	global $Spalten, $ausdatum, $con, $debug, $GlobalZeileProStunde, $error_messages;

	/////////////////////////////////////////////////////////////
	// beginnt die erste schicht vor dem heutigen tag und geht dar�ber hinaus
	/////////////////////////////////////////////////////////////
	$SQLSonder = "SELECT `SID`, `DateS`, `DateE` , `Len`, `Man` FROM `Shifts` " .
	"WHERE ((`RID` = '$raum') AND (`DateE` > '$ausdatum 23:59:59') AND " .
	"(`DateS` < '$ausdatum 00:00:00') ) ORDER BY `DateS`;";
	$ErgSonder = mysql_query($SQLSonder, $con);
	if ((mysql_num_rows($ErgSonder) > 1)) {
		if (funktion_isLinkAllowed("admin/schichtplan.php") === TRUE) {
			echo "<h1>" . Get_Text("pub_schichtplan_colision") . "</h1> ";
			for ($i = 0; $i < mysql_num_rows($ErgSonder); $i++) {
				echo "<a href=\"./../admin/schichtplan.php?action=change&SID=" .
				mysql_result($ErgSonder, $i, "SID") . "\">" .
				mysql_result($ErgSonder, $i, "DateS") .
				" '" . mysql_result($ErgSonder, $i, "Man") . "' (RID $raum) (00-24)" .
				"</a><br />\n";
			}
		}
	}
	elseif ((mysql_num_rows($ErgSonder) == 1)) {
		$Spalten[0] .= "<td valign=\"top\" rowspan=\"" . (24 * $GlobalZeileProStunde) . "\">\n" .
		"<h3>&uarr;&uarr;&uarr;</h3>" .
		Ausgabe_Feld_Inhalt(mysql_result($ErgSonder, 0, "SID"), mysql_result($ErgSonder, 0, "Man")) .
		"<h3>&darr;&darr;&darr;</h3>" .
		"\n</td>\n";
		return;
	}

	$ZeitZeiger = 0;

	/////////////////////////////////////////////////////////////
	// beginnt die erste schicht vor dem heutigen tag?
	/////////////////////////////////////////////////////////////
	$SQLSonder = "SELECT `SID`, `DateS`, `DateE` , `Len`, `Man` FROM `Shifts` " .
	"WHERE ((`RID` = '$raum') AND (`DateE` > '$ausdatum 00:00:00') AND " .
	"(`DateS` < '$ausdatum 00:00:00') ) ORDER BY `DateS`;";
	$ErgSonder = mysql_query($SQLSonder, $con);
	if ((mysql_num_rows($ErgSonder) > 1)) {
		if (funktion_isLinkAllowed("admin/schichtplan.php") === TRUE) {
			echo "<h1>" . Get_Text("pub_schichtplan_colision") . "</h1> ";
			for ($i = 0; $i < mysql_num_rows($ErgSonder); $i++) {
				echo "<a href=\"./../admin/schichtplan.php?action=change&SID=" .
				mysql_result($ErgSonder, $i, "SID") . "\">" .
				mysql_result($ErgSonder, $i, "DateS") .
				" '" . mysql_result($ErgSonder, $i, "Man") . "' (RID $raum) (00-xx)" .
				"</a><br />\n";
			}
		}
	}
	elseif ((mysql_num_rows($ErgSonder) == 1)) {
		$ZeitZeiger = substr(mysql_result($ErgSonder, 0, "DateE"), 11, 2) + (substr(mysql_result($ErgSonder, 0, "DateE"), 14, 2) / 60);
		$Spalten[0] .= "<td valign=\"top\" rowspan=\"" . ($ZeitZeiger * $GlobalZeileProStunde) . "\">\n" .
		"<h3>&uarr;&uarr;&uarr;</h3>" .
		Ausgabe_Feld_Inhalt(mysql_result($ErgSonder, 0, "SID"), mysql_result($ErgSonder, 0, "Man")) .
		"\n</td>\n";
	}

	/////////////////////////////////////////////////////////////
	// gibt die schichten f�r den tag aus
	/////////////////////////////////////////////////////////////
	$SQL = "SELECT `SID`, `DateS`, `Len`, `Man` FROM `Shifts` " .
	"WHERE ((`RID` = '$raum') and " .
	"(`DateS` >= '$ausdatum $ZeitZeiger:00:00') and " .
	"(`DateS` like '$ausdatum%')) ORDER BY `DateS`;";
	$Erg = mysql_query($SQL, $con);
	for ($i = 0; $i < mysql_num_rows($Erg); ++ $i) {
		$ZeitPos = substr(mysql_result($Erg, $i, "DateS"), 11, 2) + (substr(mysql_result($Erg, $i, "DateS"), 14, 2) / 60);
		$len = mysql_result($Erg, $i, "Len");

		if ($len <= 0)
			array_push($error_messages, "Error in shift denition SID=" . mysql_result($Erg, $i, "SID") . " Len=$len");

		if ($ZeitZeiger < $ZeitPos) {
			$Spalten[$ZeitZeiger * $GlobalZeileProStunde] .= "<td valign=\"top\" rowspan=\"" . (($ZeitPos - $ZeitZeiger) * $GlobalZeileProStunde) . "\">&nbsp;</td>\n";

			$ZeitZeiger += $ZeitPos - $ZeitZeiger;
		}
		if ($ZeitZeiger == $ZeitPos) {
			//sonderfall wenn die schicht �ber dei 24 stunden hinaus geht
			// (eintrag abk�rzen, pfeiel ausgeben)
			$Spalten[$ZeitZeiger * $GlobalZeileProStunde] .= "<td valign=\"top\" rowspan=\"" .
			 ((($len + $ZeitZeiger) ? $len : 24 - $len + $ZeitZeiger) * $GlobalZeileProStunde) .
			"\">\n" .
			"" .
			Ausgabe_Feld_Inhalt(mysql_result($Erg, $i, "SID"), mysql_result($Erg, $i, "Man")) .
			 ((($ZeitZeiger + $len) > 24) ? "<h3>&darr;&darr;&darr;</h3>" : "") .
			"\n</td>\n";
			$ZeitZeiger += $len;
		} else {
			echo "<h1>" . Get_Text("pub_schichtplan_colision") . "</h1> ";
			echo "<a href=\"./../admin/schichtplan.php?action=change&SID=" .
			mysql_result($Erg, $i, "SID") . "\">" .
			mysql_result($Erg, $i, "DateS") .
			" '" . mysql_result($Erg, $i, "Man") . "' " .
			" (" . mysql_result($Erg, $i, "SID") . " R$raum) (xx-xx)</a><br /><br />";
		}
	}
	if ($ZeitZeiger < 24)
		$Spalten[($ZeitZeiger * $GlobalZeileProStunde)] .= "<td valign=\"top\" rowspan=\"" .
		 ((24 - $ZeitZeiger) * $GlobalZeileProStunde) .
		"\">&nbsp;</td>\n";
} // function CreateRoomShifts

/*#######################################################
#  Ausgabe der freien schichten      #
#######################################################*/
function showEmptyShifts() {
	global $con, $debug, $RoomID, $gmdateOffset;

	echo "<table border=\"1\">\n";
	echo "<tr>\n";
	echo "<th>" . Get_Text("inc_schicht_date") . "</th>\n";
	echo "<th>" . Get_Text("inc_schicht_time") . "</th>\n";
	echo "<th>" . Get_Text("inc_schicht_room") . "</th>\n";
	echo "<th>" . Get_Text("inc_schicht_commend") . "</th>\n";
	echo "</tr>\n";

	$sql = "SELECT `SID`, `DateS`, `Man`, `RID` FROM `Shifts` " .
	"WHERE (`Shifts`.`DateS`>='" . gmdate("Y-m-d H:i:s", time() + $gmdateOffset) . "') " .
	"ORDER BY `DateS`, `RID`;";
	$Erg = mysql_query($sql, $con);

	$angezeigt = 0;
	for ($i = 0;($i < mysql_num_rows($Erg)) && ($angezeigt < 15); $i++)
		if (isset ($RoomID[mysql_result($Erg, $i, "RID")]))
			if ($RoomID[mysql_result($Erg, $i, "RID")] != "") {
				$Sql2 = "SELECT `UID` FROM `ShiftEntry` " .
				"WHERE `SID`=" . mysql_result($Erg, $i, "SID") . " AND " .
				"`UID`='0';";
				$Erg2 = mysql_query($Sql2, $con);

				if (mysql_num_rows($Erg2) > 0) {
					$angezeigt++;
					echo "<tr>\n";
					echo "<td>" . substr(mysql_result($Erg, $i, "DateS"), 0, 10) . "</td>\n";
					echo "<td>" . substr(mysql_result($Erg, $i, "DateS"), 11) . "</td>\n";
					echo "<td>" . $RoomID[mysql_result($Erg, $i, "RID")] . "</td>\n";
					echo "<td>" .
					ausgabe_Feld_Inhalt(mysql_result($Erg, $i, "SID"), mysql_result($Erg, $i, "Man")) .
					"</td>\n";
					echo "</tr>\n";
				}
			}

	echo "</table>\n";

} //function showEmptyShifts

/*#######################################################
#  Gibt die anzahl der Schichten im Raum zur�ck  #
#######################################################*/
function SummRoomShifts($raum) {
	global $ausdatum, $con, $debug, $GlobalZeileProStunde;

	$SQLSonder = "SELECT `SID`, `DateS`, `Len`, `Man` FROM `Shifts` " .
	"WHERE ((`RID` = '$raum') AND (`DateE` >= '$ausdatum 00:00:00') AND " .
	"(`DateS` <= '$ausdatum 23:59:59') ) ORDER BY `DateS`;";

	$ErgSonder = mysql_query($SQLSonder, $con);

	return mysql_num_rows($ErgSonder);
}

function DatumUm1TagErhoehen($Datum) {
	$Jahr = substr($Datum, 0, 4);
	$Monat = substr($Datum, 5, 2);
	$Tag = substr($Datum, 8, 2);

	$Tag++;

	switch ($Monat) {
		case 1 :
			$Mmax = 31;
			break;
		case 2 :
			$Mmax = 28;
			break;
		case 3 :
			$Mmax = 31;
			break;
		case 4 :
			$Mmax = 30;
			break;
		case 5 :
			$Mmax = 31;
			break;
		case 6 :
			$Mmax = 30;
			break;
		case 7 :
			$Mmax = 31;
			break;
		case 8 :
			$Mmax = 31;
			break;
		case 9 :
			$Mmax = 30;
			break;
		case 10 :
			$Mmax = 31;
			break;
		case 11 :
			$Mmax = 30;
			break;
		case 12 :
			$Mmax = 31;
			break;
	}

	if ($Tag > $Mmax) {
		$Tag = 1;
		$Monat++;
	}

	if ($Monat > 12) {
		$Monat = 1;
		$Jahr++;
	}

	$Tag = strlen($Tag) == 1 ? "0" . $Tag : $Tag;
	$Monat = strlen($Monat) == 1 ? "0" . $Monat : $Monat;

	return ("$Jahr-$Monat-$Tag");
}
?>
