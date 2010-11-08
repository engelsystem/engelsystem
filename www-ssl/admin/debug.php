<?PHP

$title = "Debug-Liste";
$header = "Datenbank-Auszug";
include ("../../includes/header.php");
include ("../../includes/funktion_db_list.php");

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

echo "<br>\n";
funktion_db_element_list_2row( "Tshirt-Size aller engel",
				"SELECT `Size`, COUNT(`Size`) FROM `User` GROUP BY `Size`"); 

echo "<br>\n";
funktion_db_element_list_2row( "Tshirt ausgegeben",
				"SELECT `Size`, COUNT(`Size`) FROM `User` WHERE `Tshirt`='1' GROUP BY `Size`"); 

echo "<br>\n";
funktion_db_element_list_2row( "Tshirt nicht  ausgegeben (Gekommen=1)", 
				"SELECT COUNT(`Size`), `Size` FROM `User` WHERE `Gekommen`='1' and `Tshirt`='0' GROUP BY `Size`");

echo "<br>\n";
funktion_db_element_list_2row( "Hometown", 
				"SELECT COUNT(`Hometown`), `Hometown` FROM `User` GROUP BY `Hometown`");

echo "<br>\n";
funktion_db_element_list_2row( "Engeltypen", 
					"SELECT COUNT(`Art`), `Art` FROM `User` GROUP BY `Art`");


include ("../../includes/footer.php");
?>

