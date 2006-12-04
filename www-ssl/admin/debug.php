<?PHP

$title = "Debug-Liste";
$header = "Datenbank-Auszug";
include ("./inc/header.php");
include ("./inc/funktion_db_list.php");

echo "<h1>Web Counter</h1>";
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


echo "<h1>Tshirt-Size</h1>";
$SQL="SELECT `Size`, COUNT(`Size`) FROM `User` GROUP BY `Size`";
$Erg = mysql_query($SQL, $con);
echo mysql_error($con);
$rowcount = mysql_num_rows($Erg);

for ($i=0; $i<$rowcount; $i++)
	echo mysql_result($Erg, $i, 1). "x '".  mysql_result($Erg, $i, 0). "'<br>\n";


include ("./inc/footer.php");
?>

