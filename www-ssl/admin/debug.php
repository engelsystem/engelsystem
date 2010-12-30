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

echo "<hr>\n";
funktion_db_element_list_2row( "Tshirt-Size aller engel",
				"SELECT `Size`, COUNT(`Size`) FROM `User` GROUP BY `Size`"); 
echo "<br>\n";
funktion_db_element_list_2row( "Tshirt ausgegeben",
				"SELECT `Size`, COUNT(`Size`) FROM `User` WHERE `Tshirt`='1' GROUP BY `Size`"); 
echo "<br>\n";
funktion_db_element_list_2row( "Tshirt nicht  ausgegeben (Gekommen=1)", 
				"SELECT COUNT(`Size`), `Size` FROM `User` WHERE `Gekommen`='1' and `Tshirt`='0' GROUP BY `Size`");

echo "<hr>\n";
funktion_db_element_list_2row( "Hometown", 
				"SELECT COUNT(`Hometown`), `Hometown` FROM `User` GROUP BY `Hometown`");
echo "<br>\n";
funktion_db_element_list_2row( "Engeltypen", 
					"SELECT COUNT(`Art`), `Art` FROM `User` GROUP BY `Art`");

echo "<hr>\n";
funktion_db_element_list_2row( "Gesamte Arbeit",
					"SELECT COUNT(*) AS `Count [x]`, SUM(Shifts.Len) as `Sum [h]` from Shifts LEFT JOIN ShiftEntry USING(SID)");
echo "<br>\n";
funktion_db_element_list_2row( "Geleisteter Arbeit",
					"SELECT COUNT(*) AS `Count [x]`, SUM(Shifts.Len) as `Sum [h]` from Shifts LEFT JOIN ShiftEntry USING(SID) WHERE (ShiftEntry.UID!=0)");

echo "<hr>\n";
funktion_db_element_list_2row( "Gesamte Arbeit (Ohne Raum aufabau (RID=7)",
					"SELECT COUNT(*) AS `Count [x]`, SUM(Shifts.Len) as `Sum [h]` from Shifts LEFT JOIN ShiftEntry USING(SID) WHERE (Shifts.RID!=7)");
echo "<br>\n";
funktion_db_element_list_2row( "Geleisteter Arbeit (Ohne Raum aufabau (RID=7)",
					"SELECT COUNT(*) AS `Count [x]`, SUM(Shifts.Len) as `Sum [h]` from Shifts LEFT JOIN ShiftEntry USING(SID) WHERE (ShiftEntry.UID!=0) AND (Shifts.RID!=7)");




include ("../../includes/footer.php");
?>

