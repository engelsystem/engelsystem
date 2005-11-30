<?PHP

$title = "Erzengel";
$header = "Index";
include ("./inc/header.php");
include ("./inc/funktion_db_list.php");

echo "Hallo Erzengel ".$_SESSION['Nick'].",<br>\n";

?>

du bist jetzt im Erzengel-Bereich. Hier kannst du die Engel-Verwaltung vornehmen.<br><br>

Bitte melde dich <a href="../logout.php">hier</a> nach getaner Arbeit immer ab, damit kein anderer hier &Auml;nderungen vornehmen kann.

<?PHP
include ("./inc/footer.php");
?>

