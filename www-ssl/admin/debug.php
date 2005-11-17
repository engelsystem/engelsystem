<?PHP

$title = "Debug-Liste";
$header = "Datenbank-Auszug";
include ("./inc/header.php");
include ("./inc/funktion_db_list.php");

echo "<h1>Counter</h1> <br>";
funktion_db_list("Counter");

/*
echo "<h1>Raeume</h1> <br>";
funktion_db_list("Raeume");

echo "<h1>Schichtbelegung</h1> <br>";
funktion_db_list("Schichtbelegung");

echo "<h1>Schichtplan</h1> <br>Hier findest du alle bisher eingetragenen Schichten:";
funktion_db_list("Schichtplan");

echo "<h1>User</h1> <br>";
funktion_db_list("User");

echo "<h1>News</h1> <br>";
funktion_db_list("News");

echo "<h1>FAQ</h1> <br>";
funktion_db_list("FAQ");

echo "Deaktiviert";
*/

include ("./inc/footer.php");
?>

