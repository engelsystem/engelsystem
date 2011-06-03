<?php
function admin_log() {
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
	return $html;
}
?>

