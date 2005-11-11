<?PHP

$title = "Debug-Liste";
$header = "Datenbank-Auszug";
include ("./inc/header.php");
include ("./inc/funktion_db_list.php");


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

*/

echo "Deaktiviert<br>\n";

$erg = mysql_query("SHOW COLUMNS FROM `UserCVS`");
echo mysql_error();

if (mysql_num_rows($erg) > 0) 
{
/*	while ($row = mysql_fetch_assoc($erg))
	{
		print_r($erg);
	}
*/
}
for( $i=1; $i<mysql_num_rows($erg); $i++)
{
	echo "";
	echo  mysql_result( $erg, $i, "Field");
	echo  mysql_result( $erg, $i, "Default");
	echo "<br>";
}

include ("./inc/footer.php");
?>

