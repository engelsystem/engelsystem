<?PHP

$title = "User-Liste";
$header = "Editieren der Engelliste";
include ("../../includes/header.php");
include ("../../includes/funktion_db_list.php");

if (IsSet($_GET["enterUID"]))
{ 
	// UserID wurde mit uebergeben --> Aendern...

	echo "Hallo,<br>".
	 	"hier kannst du den Eintrag &auml;ndern. Unter dem Punkt 'Gekommen' ".
		"wird der Engel als anwesend markiert, ein Ja bei Aktiv bedeutet, ".
		"dass der Engel aktiv war und damit ein Anspruch auf ein T-Shirt hat. ".
		"Wenn T-Shirt ein 'Ja' enth&auml;lt, bedeutet dies, dass der Engel ".
		"bereits sein T-Shirt erhalten hat.<br><br>\n";

	echo "<form action=\"./userSaveNormal.php?action=change\" method=\"POST\">\n";
	echo "<table border=\"0\">\n";
	echo "<input type=\"hidden\" name=\"Type\" value=\"Normal\">\n";

	$SQL = "SELECT * FROM `User` WHERE `UID`='". $_GET["enterUID"]. "'";
	$Erg = mysql_query($SQL, $con);
		
	if (mysql_num_rows($Erg) != 1) 
		echo "<tr><td>Sorry, der Engel (UID=". $_GET["enterUID"]. 
			") wurde in der Liste nicht gefunden.</td></tr>";
	else
	{
		echo "<tr><td>\n";
		echo "<table>\n";
		echo "  <tr><td>Nick</td><td>".
			"<input type=\"text\" size=\"40\" name=\"eNick\" value=\"".
			mysql_result($Erg, 0, "Nick")."\"></td></tr>\n";
		echo "  <tr><td>lastLogIn</td><td>".
			"<input type=\"text\" size=\"20\" name=\"elastLogIn\" value=\"".
			mysql_result($Erg, 0, "lastLogIn"). "\" disabled></td></tr>\n";
		echo "  <tr><td>Name</td><td>".
			"<input type=\"text\" size=\"40\" name=\"eName\" value=\"".
			mysql_result($Erg, 0, "Name")."\"></td></tr>\n";
		echo "  <tr><td>Vorname</td><td>".
			"<input type=\"text\" size=\"40\" name=\"eVorname\" value=\"".
			mysql_result($Erg, 0, "Vorname")."\"></td></tr>\n";
		echo "  <tr><td>Alter</td><td>".
			"<input type=\"text\" size=\"5\" name=\"eAlter\" value=\"".
			mysql_result($Erg, 0, "Alter")."\"></td></tr>\n";
		echo "  <tr><td>Telefon</td><td>".
			"<input type=\"text\" size=\"40\" name=\"eTelefon\" value=\"".
			mysql_result($Erg, 0, "Telefon")."\"></td></tr>\n";
		echo "  <tr><td>Handy</td><td>".
			"<input type=\"text\" size=\"40\" name=\"eHandy\" value=\"".
			mysql_result($Erg, 0, "Handy")."\"></td></tr>\n";
		echo "  <tr><td>DECT</td><td>".
			"<input type=\"text\" size=\"4\" name=\"eDECT\" value=\"".
			mysql_result($Erg, 0, "DECT")."\"></td></tr>\n";
		echo "  <tr><td>email</td><td>".
			"<input type=\"text\" size=\"40\" name=\"eemail\" value=\"".
			mysql_result($Erg, 0, "email")."\"></td></tr>\n";
		echo "  <tr><td>ICQ</td><td>".
			"<input type=\"text\" size=\"40\" name=\"eICQ\" value=\"".
			mysql_result($Erg, 0, "ICQ")."\"></td></tr>\n";
		echo "  <tr><td>jabber</td><td>".
			"<input type=\"text\" size=\"40\" name=\"ejabber\" value=\"".
			mysql_result($Erg, 0, "jabber")."\"></td></tr>\n";
		echo "  <tr><td>Size</td><td>".
			"<input type=\"text\" size=\"5\" name=\"eSize\" value=\"".
			mysql_result($Erg, 0, "Size")."\"></td></tr>\n";
		echo "  <tr><td>Passwort</td><td>".
			"<a href=\"./userSaveNormal.php?action=newpw&eUID="
			.mysql_result($Erg, 0, "UID")."\">neues Kennwort setzen</a></td></tr>\n";
 
		// Gekommen? 
		echo "  <tr><td>Gekommen</td><td>\n";
		echo "      <input type=\"radio\" name=\"eGekommen\" value=\"0\"";
		if (mysql_result($Erg, 0, "Gekommen")=='0') 
			echo " checked"; 
		echo ">No \n";
		echo "      <input type=\"radio\" name=\"eGekommen\" value=\"1\"";
		if (mysql_result($Erg, 0, "Gekommen")=='1') 
			echo " checked";
		echo ">Yes \n";
		echo "</td></tr>\n";

		// Aktiv?
		echo "  <tr><td>Aktiv</td><td>\n";
		echo "      <input type=\"radio\" name=\"eAktiv\" value=\"0\"";
		if (mysql_result($Erg, 0, "Aktiv")=='0') 
			echo " checked";
		echo ">No \n";
		echo "      <input type=\"radio\" name=\"eAktiv\" value=\"1\"";
		if (mysql_result($Erg, 0, "Aktiv")=='1') 
			echo " checked"; 
		echo ">Yes \n";
		echo "</td></tr>\n";

		// T-Shirt bekommen? 
		echo "  <tr><td>T-Shirt</td><td>\n";
		echo "      <input type=\"radio\" name=\"eTshirt\" value=\"0\"";
		if (mysql_result($Erg, 0, "Tshirt")=='0')
			echo " checked";
		echo ">No \n";
		echo "      <input type=\"radio\" name=\"eTshirt\" value=\"1\"";
		if (mysql_result($Erg, 0, "Tshirt")=='1') 
			echo " checked";
		echo ">Yes \n";
		echo "</td></tr>\n";

		echo "  <tr><td>Hometown</td><td>".
			"<input type=\"text\" size=\"40\" name=\"Hometown\" value=\"".
			mysql_result($Erg, 0, "Hometown")."\"></td></tr>\n";
		
		echo "</table>\n</td><td valign=\"top\">". displayavatar($_GET["enterUID"], FALSE). "</td></tr>";
	}

	echo "</td></tr>\n";
	echo "</table>\n<br>\n";
	echo "<input type=\"hidden\" name=\"enterUID\" value=\"". $_GET["enterUID"]. "\">\n";
	echo "<input type=\"submit\" value=\"sichern...\">\n";
	echo "</form>";

	echo "<form action=\"./userSaveNormal.php?action=delete\" method=\"POST\">\n";
	echo "<input type=\"hidden\" name=\"enterUID\" value=\"". $_GET["enterUID"]. "\">\n";
	echo "<input type=\"submit\" value=\"l&ouml;schen...\">\n";
	echo "</form>";

	
	echo "<hr>";
	funktion_db_element_list_2row( 
		"Freeloader Shifts", 
		"SELECT `Remove_Time`, `Length`, `Comment` FROM `ShiftFreeloader` WHERE UID=". $_GET["enterUID"]); 
}

include ("../../includes/footer.php");
?>


