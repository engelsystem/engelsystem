<?php


// Funktionen gibt es nicht auf allen Rechnern
echo "<h4>Engel online</h4>";

$SQL = "SELECT UID, Nick, lastLogIn " .
"FROM User " .
"WHERE (`lastLogIn` > '" . (time() - 60 * 60) . "' AND NOT (UID=" . $_SESSION['UID'] . ")) " .
"ORDER BY lastLogIn DESC;";

$Erg = mysql_query($SQL, $con);

echo "<ul>";

for ($i = 0; $i < mysql_num_rows($Erg); $i++) {
	echo "<li>";

	if ($_SESSION['UID'] > 0)
		echo DisplayAvatar(mysql_result($Erg, $i, "UID"));

	// Show Admin Page
	echo funktion_isLinkAllowed_addLink_OrLinkText("admin/userChangeNormal.php?enterUID=" . mysql_result($Erg, $i, "UID") . "&Type=Normal", mysql_result($Erg, $i, "Nick"));

	$timestamp = mktime($hour, $minute, $second, $month, $day, $year);

	$Tlog = time() - mysql_result($Erg, $i, "lastLogIn");

	echo " " . date("i:s", $Tlog);
	echo "</li>\n";
}

echo "</ul>";
?>
