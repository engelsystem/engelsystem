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
		list ($frage_de, $frage_en) = split('<br />', mysql_result($Erg, $n, "Frage"));
		list ($antwort_de, $antwort_en) = split('<br />', mysql_result($Erg, $n, "Antwort"));
		echo "<dl>";
		if ($_SESSION['Sprache'] == "DE") {
			echo "<dt>" . $frage_de . "</dt>";
			echo "<dd>" . $antwort_de . "</dd>";
		} else {
			echo "<dt>" . $frage_en . "</dt>";
			echo "<dd>" . $antwort_en . "</dd>";
		}
		echo "</dl>";
	}
}

include "includes/footer.php";
?>
