<?php
$title = "Index";
$header = "FAQ";
include ("../../camp2011/includes/header.php");


$SQL = "SELECT * FROM `FAQ`";
$Erg = mysql_query($SQL, $con);

// anzahl zeilen
$Zeilen  = mysql_num_rows($Erg);

for ($n = 0 ; $n < $Zeilen ; $n++) {
  if (mysql_result($Erg, $n, "Antwort")!="") {
    echo "<p class='question'>".mysql_result($Erg, $n, "Frage")."</p>";
    echo "<p class='answetion'>".mysql_result($Erg, $n, "Antwort")."</p>";
  }
}

include ("../../camp2011/includes/footer.php");
?>

