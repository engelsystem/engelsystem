<?php
require_once ('../bootstrap.php');

$title = "Erzengel";
$header = "Index";
include ("includes/header.php");
include ("includes/funktion_db_list.php");

echo "Hallo Erzengel " . $_SESSION['Nick'] . ",<br />\n";
?>

du bist jetzt im Erzengel-Bereich. Hier kannst du die Engel-Verwaltung vornehmen.<br /><br />

Bitte melde dich <a href="../logout.php">hier</a> nach getaner Arbeit immer ab, damit kein anderer hier &Auml;nderungen vornehmen kann.

<?php


include ("includes/footer.php");
?>

