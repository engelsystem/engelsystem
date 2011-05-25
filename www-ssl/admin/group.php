<?PHP

$title = "User-Liste";
$header = "Editieren der Engelliste";
include ("../../../camp2011/includes/header.php");
include ("../../../camp2011/includes/funktion_db_list.php");

if (!IsSet($_GET["enterGID"]))
{
	// Userliste, keine UID uebergeben...

	$SQL = "SELECT * FROM `UserGroups` ORDER BY `Name` ASC";
	$Erg = mysql_query($SQL, $con);
	echo mysql_error($con);

	// anzahl zeilen
	$Zeilen  = mysql_num_rows($Erg);

	echo "<table class=\"border\" cellpadding=\"2\" cellspacing=\"1\">\n";
	echo "<tr class=\"contenttopic\">\n";
	echo "\t<td>Groupname</td>\n";
	echo "\t<td>Link</td>\n";
	echo "</tr>\n";

	for ($n = 0 ; $n < $Zeilen ; $n++) {
		echo "<tr class=\"content\">\n";
		echo "\t<td>".mysql_result($Erg, $n, "Name")."</td>\n";
		
		echo "<td><a href=\"./userChangeSecure.php?enterUID=".
			mysql_result($Erg, $n, "UID")."&Type=Secure\">change</a></td>\n";
		echo "</tr>\n";
	}
	
	// new form
	echo "<tr class=\"content\">\n";
	echo "\t<form action=\"userSaveSecure.php?new=newGroup\"  method=\"POST\">\n";
	echo "\t\t<td><input name=\"GroupName\" type=\"text\" value=\"--new group--\"></td>\n";
	echo "\t\t<td><input type=\"submit\" name=\"Send\" value=\"Save\"></td>\n";
	echo "\t</form>\n";
	echo "</tr>\n";

	echo "\t</table>\n";
	// Ende Userliste
} 

include ("../../../camp2011/includes/footer.php");
?>


