<?php
function admin_log() {
	require_once ("includes/funktion_db_list.php");

	$html = "";
	$SQL = "SELECT * FROM `ChangeLog` ORDER BY `Time` DESC LIMIT 0,10000";
	$Erg = sql_query($SQL);

	if (mysql_num_rows($Erg) > 0) {
		$html .= "<table border=1>\n";
		$html .= "<tr>\n\t<th>Time</th>\n\t<th>User</th>\n\t<th>Commend</th>\n\t<th>SQL Command</th>\n</tr>\n";
		for ($n = 0; $n < mysql_num_rows($Erg); $n++) {
			$html .= "<tr>\n";
			$html .= "\t<td>" . mysql_result($Erg, $n, "Time") . "</td>\n";
			$html .= "\t<td>" . UID2Nick(mysql_result($Erg, $n, "UID")) . displayavatar(mysql_result($Erg, $n, "UID")) . "</td>\n";
			$html .= "\t<td>" . mysql_result($Erg, $n, "Commend") . "</td>\n";
			$html .= "\t<td>" . mysql_result($Erg, $n, "SQLCommad") . "</td>\n";
			$html .= "</tr>\n";
		}
		$html .= "</table>\n";
	} else {
		$html .= "Log is empty...";
	}
	$html .= "<hr />";

	$html .= "<h1>Web Counter</h1>";
	$html .= funktion_db_list("Counter");

	/*
	$html .= "<h1>Raeume</h1> <br />";
	funktion_db_list("Raeume");
	
	$html .= "<h1>Schichtbelegung</h1> <br />";
	funktion_db_list("Schichtbelegung");
	
	$html .= "<h1>Schichtplan</h1> <br />Hier findest du alle bisher eingetragenen Schichten:";
	funktion_db_list("Schichtplan");
	
	$html .= "<h1>User</h1> <br />";
	funktion_db_list("User");
	
	$html .= "<h1>News</h1> <br />";
	funktion_db_list("News");
	
	$html .= "<h1>FAQ</h1> <br />";
	funktion_db_list("FAQ");
	
	$html .= "Deaktiviert";
	*/

	$html .= "<hr>\n";
	$html .= funktion_db_element_list_2row("Tshirt-Size aller engel", "SELECT `Size`, COUNT(`Size`) FROM `User` GROUP BY `Size`");
	$html .= "<br />\n";
	$html .= funktion_db_element_list_2row("Tshirt ausgegeben", "SELECT `Size`, COUNT(`Size`) FROM `User` WHERE `Tshirt`='1' GROUP BY `Size`");
	$html .= "<br />\n";
	$html .= funktion_db_element_list_2row("Tshirt nicht  ausgegeben (Gekommen=1)", "SELECT COUNT(`Size`), `Size` FROM `User` WHERE `Gekommen`='1' and `Tshirt`='0' GROUP BY `Size`");

	$html .= "<hr>\n";
	$html .= funktion_db_element_list_2row("Hometown", "SELECT COUNT(`Hometown`), `Hometown` FROM `User` GROUP BY `Hometown`");
	$html .= "<br />\n";
	$html .= funktion_db_element_list_2row("Engeltypen", "SELECT COUNT(`Art`), `Art` FROM `User` GROUP BY `Art`");

	$html .= "<hr>\n";
	$html .= funktion_db_element_list_2row("Gesamte Arbeit", "SELECT COUNT(*) AS `Count [x]`, SUM(Shifts.Len) as `Sum [h]` from Shifts LEFT JOIN ShiftEntry USING(SID)");
	$html .= "<br />\n";
	$html .= funktion_db_element_list_2row("Geleistete Arbeit", "SELECT COUNT(*) AS `Count [x]`, SUM(Shifts.Len) as `Sum [h]` from Shifts LEFT JOIN ShiftEntry USING(SID) WHERE (ShiftEntry.UID!=0)");

	$html .= "<hr>\n";
	$html .= funktion_db_element_list_2row("Gesamte Arbeit (Ohne Raum Aufbau (RID=7)", "SELECT COUNT(*) AS `Count [x]`, SUM(Shifts.Len) as `Sum [h]` from Shifts LEFT JOIN ShiftEntry USING(SID) WHERE (Shifts.RID!=7)");
	$html .= "<br />\n";
	$html .= funktion_db_element_list_2row("Geleistete Arbeit (Ohne Raum Aufbau (RID=7)", "SELECT COUNT(*) AS `Count [x]`, SUM(Shifts.Len) as `Sum [h]` from Shifts LEFT JOIN ShiftEntry USING(SID) WHERE (ShiftEntry.UID!=0) AND (Shifts.RID!=7)");

	return $html;
}
?>

