<?PHP

$title = "ChangeLog";
$header = "Datenbank-Auszug";
include ("../../../camp2011/includes/header.php");

$SQL = "SELECT * FROM `ChangeLog` ORDER BY `Time` DESC LIMIT 0,10000";
$Erg = mysql_query($SQL, $con);

echo mysql_error($con);

echo "<table border=1>\n";
echo "<tr>\n\t<th>Time</th>\n\t<th>User</th>\n\t<th>Commend</th>\n\t<th>SQL Commad</th>\n</tr>\n";

for ($n = 0 ; $n < mysql_num_rows($Erg) ; $n++)
{
	echo "<tr>\n";
	echo "\t<td>". mysql_result( $Erg, $n, "Time"). "</td>\n";
	echo "\t<td>". UID2Nick(mysql_result( $Erg, $n, "UID")). displayavatar(mysql_result( $Erg, $n, "UID")). "</td>\n";
	echo "\t<td>". mysql_result( $Erg, $n, "Commend"). "</td>\n";
	echo "\t<td>". mysql_result( $Erg, $n, "SQLCommad"). "</td>\n";
	echo "</tr>\n";
}

echo "</table>\n";

include ("../../../camp2011/includes/footer.php");
?>

