<?php
require_once ('bootstrap.php');

$title = "FAQ";
$header = "FAQ";
include "includes/header.php";

$SQL = "SELECT * FROM `FAQ`";
$Erg = mysql_query($SQL, $con);

// anzahl Zeilen
$Zeilen = mysql_num_rows($Erg);

for ($n = 0; $n < $Zeilen; $n++) {
	if (mysql_result($Erg, $n, "Antwort") != "") {
		echo "<dl>";
		echo "<dt>" . mysql_result($Erg, $n, "Frage") . "</dt>";
		echo "<dd>" . mysql_result($Erg, $n, "Antwort") . "</dd>";
		echo "</dl>";
	}
}

include "includes/footer.php";
?>
