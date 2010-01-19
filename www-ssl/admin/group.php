<?PHP

$title = "User-Liste";
$header = "Editieren der Engelliste";
include ("../../includes/header.php");
include ("../../includes/funktion_db_list.php");

if (!IsSet($_GET["enterGID"]))
{
	// Userliste, keine UID uebergeben...

	$SQL = "SELECT * FROM `UserGroups` ORDER BY `Name` ASC";
	$Erg = mysql_query($SQL, $con);
	echo mysql_error($con);

	// anzahl zeilen
	$Zeilen  = mysql_num_rows($Erg);

	echo "<table width=\"100%\" class=\"border\" cellpadding=\"2\" cellspacing=\"1\">\n";
	echo "<tr class=\"contenttopic\">\n";
	echo "\t<td>Groupname</td>\n";
	echo "\t<td>-</td>\n";
	echo "</tr>\n";

	for ($n = 0 ; $n < $Zeilen ; $n++) {
		echo "<tr class=\"content\">\n";
		echo "\t<td>".mysql_result($Erg, $n, "Name")."</td>\n";
		
		echo "<td><a href=\"./userChangeSecure.php?enterUID=".
			mysql_result($Erg, $n, "UID")."&Type=Secure\">change</a></td>\n";
		echo "</tr>\n";
	}
	echo "\t</table>\n";
	// Ende Userliste
} 

include ("../../includes/footer.php");
?>


