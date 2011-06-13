<?php


// Gibt eine Tabelle generisch als HTML aus
function funktion_db_list($Table_Name) {
	global $con;

	$html = "";

	$SQL = "SELECT * FROM `" . $Table_Name . "`";
	$Erg = mysql_query($SQL, $con);

	// anzahl zeilen
	$Zeilen = mysql_num_rows($Erg);

	$Anzahl_Felder = mysql_num_fields($Erg);

	$html .= "<table class=\"border\" cellpadding=\"2\" cellspacing=\"1\">";
	$html .= "<caption>DB: $Table_Name</caption>";

	$html .= "<tr class=\"contenttopic\">";
	for ($m = 0; $m < $Anzahl_Felder; $m++) {
		$html .= "<th>" . mysql_field_name($Erg, $m) . "</th>";
	}
	$html .= "</tr>";

	for ($n = 0; $n < $Zeilen; $n++) {
		$html .= "<tr class=\"content\">";
		for ($m = 0; $m < $Anzahl_Felder; $m++) {
			$html .= "<td>" . mysql_result($Erg, $n, $m) . "</td>";
		}
		$html .= "</tr>";
	}
	$html .= "</table>";
	return $html;
}

function funktion_db_element_list_2row($TopicName, $SQL) {
	$html = "";
	$html .= "<table class=\"border\" cellpadding=\"2\" cellspacing=\"1\">\n";
	$html .= "<caption>$TopicName</caption>";
	#  $html .= "<tr class=\"contenttopic\"> <td><h1>$TopicName</h1></td> </tr>\n";

	$Erg = sql_query($SQL);

	$html .= "<tr class=\"contenttopic\">";
	for ($m = 0; $m < mysql_num_fields($Erg); $m++) {
		$html .= "<th>" . mysql_field_name($Erg, $m) . "</th>";
	}
	$html .= "</tr>";

	for ($n = 0; $n < mysql_num_rows($Erg); $n++) {
		$html .= "<tr class=\"content\">";
		for ($m = 0; $m < mysql_num_fields($Erg); $m++) {
			$html .= "<td>" . mysql_result($Erg, $n, $m) . "</td>";
		}
		$html .= "</tr>";
	}
	$html .= "</table>\n";
	return $html;
}
?>
