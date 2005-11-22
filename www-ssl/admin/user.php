<?PHP

$title = "User-Liste";
$header = "Editieren der Engelliste";
include ("./inc/header.php");
include ("./inc/funktion_db_list.php");
include ("./inc/funktion_user.php");

if (!IsSet($_GET["enterUID"]))
{
	// Userliste, keine UID uebergeben...

	echo "<a href=\"../makeuser.php\">Neuen Engel eintragen</a><br><br>\n";
	
	echo "\n<a href=\"./user.php?enterUID=-1&Type=Secure\">Edit logout User</a><br><br>\n";

	if( !isset($_GET["OrderBy"]) ) $_GET["OrderBy"] = "Nick";
	$SQL = "SELECT * FROM User ORDER BY ". $_GET["OrderBy"]. " ASC";
	$Erg = mysql_query($SQL, $con);
	echo mysql_error($con);

	// anzahl zeilen
	$Zeilen  = mysql_num_rows($Erg);

	echo "Anzahl Engel: $Zeilen<br><br>\n";

	?><table width="100%" class="border" cellpadding="2" cellspacing="1"> 
	<tr class="contenttopic">
		<td><a href="<? echo $_SERVER["PHP_SELF"]; ?>?OrderBy=Nick">Nick</a></td>
		<td><a href="<? echo $_SERVER["PHP_SELF"]; ?>?OrderBy=Name">Name</a></td>
		<td><a href="<? echo $_SERVER["PHP_SELF"]; ?>?OrderBy=Vorname">Vorname</a></td>
		<td>Alter</td>
		<td>Telefon <a href="<? echo $_SERVER["PHP_SELF"]; ?>?OrderBy=email">@</a></td>
		<td><a href="<? echo $_SERVER["PHP_SELF"]; ?>?OrderBy=Size">Gr&ouml;&szlig;e</a></td>
		<td><a href="<? echo $_SERVER["PHP_SELF"]; ?>?OrderBy=Gekommen">G</a></td>
		<td><a href="<? echo $_SERVER["PHP_SELF"]; ?>?OrderBy=Aktiv">A</a></td>
		<td><a href="<? echo $_SERVER["PHP_SELF"]; ?>?OrderBy=Tshirt">T</a></td>
		<td>&Auml;nd.</td>
		<td>Secure</td>
	</tr>


	<?
	$Gekommen = 0;
	$Active = 0;
	$Tshirt = 0;
	
	for ($n = 0 ; $n < $Zeilen ; $n++) {
		echo "<tr class=\"content\">\n";
		echo "\t<td>".mysql_result($Erg, $n, "Nick"). "</td>\n";
		echo "\t<td>".mysql_result($Erg, $n, "Name")."</td>\n";
		echo "\t<td>".mysql_result($Erg, $n, "Vorname")."</td>\n";
		echo "\t<td>".mysql_result($Erg, $n, "Alter")."</td>\n";
		echo "\t<td>";
			if( strlen( mysql_result($Erg, $n, "Telefon"))>0)
				echo "\n\t\tTel: ". mysql_result($Erg, $n, "Telefon"). "<br>";
			if( strlen( mysql_result($Erg, $n, "Handy"))>0)
				echo "\n\t\tHandy: ". mysql_result($Erg, $n, "Handy"). "<br>";
			if( strlen( mysql_result($Erg, $n, "DECT"))>0)
				echo "\n\t\tDECT: <a href=\"./dect.php?custum=". mysql_result($Erg, $n, "DECT"). "\">".
					mysql_result($Erg, $n, "DECT"). "</a><br>";
			if( strlen( mysql_result($Erg, $n, "email"))>0)
				echo "\n\t\temail: <a href=\"mailto:".mysql_result($Erg, $n, "email")."\">".
					mysql_result($Erg, $n, "email")."</a><br>";
			if( strlen( mysql_result($Erg, $n, "Hometown"))>0)
				echo "\n\t\tHometown: ". mysql_result($Erg, $n, "Hometown"). "<br>";
			if( strlen( mysql_result($Erg, $n, "lastLogIn"))>0)
				echo "\n\t\tlastLogIn: ". mysql_result($Erg, $n, "lastLogIn"). "<br>";
			echo "</td>\n";
		echo "\t<td>".mysql_result($Erg, $n, "Size")."</td>\n";
		$Gekommen += mysql_result($Erg, $n, "Gekommen");
		echo "\t<td>".mysql_result($Erg, $n, "Gekommen")."</td>\n";
		$Active += mysql_result($Erg, $n, "Aktiv");
		echo "\t<td>".mysql_result($Erg, $n, "Aktiv")."</td>\n";
		$Tshirt += mysql_result($Erg, $n, "Tshirt");
		echo "\t<td>".mysql_result($Erg, $n, "Tshirt")."</td>\n";
		echo "\t<td><a href=\"./user.php?enterUID=".
			mysql_result($Erg, $n, "UID")."&Type=Normal\">&Auml;nd.</a></td>\n";
		echo "\t<td>";
		
		//check userCVS=OK
		$SQL2 = "SELECT UID FROM UserCVS WHERE (UID=". mysql_result($Erg, $n, "UID"). ")";
		$Erg2 = mysql_query($SQL2, $con);
		echo mysql_error($con);
		if( mysql_num_rows($Erg2)==0)
		{
			$SQL3 = "INSERT INTO `UserCVS` ( `UID`) VALUES ( '". mysql_result($Erg, $n, "UID"). "');";
			$Erg3 = mysql_query($SQL3, $con);
			if( $Erg3 )
				echo "was create<br>\n";
			else
				echo mysql_error($con);
		}
		echo "<a href=\"./user.php?enterUID=".
			mysql_result($Erg, $n, "UID")."&Type=Secure\">Secure</a></td>\n";
		echo "</tr>\n";
	}
	echo "<tr>".
		"<td></td><td></td><td></td><td></td><td></td><td></td>".
		"<td>$Gekommen</td><td>$Active</td><td>$Tshirt</td><td></td></tr>\n";
	echo "\t</table>\n";
	// Ende Userliste
} 
else 
{ 
	// UserID wurde mit uebergeben --> Aendern...

	echo "Hallo,<br>".
	 	"hier kannst du den Eintrag &auml;ndern. Unter dem Punkt 'Gekommen' ".
		"wird der Engel als anwesend markiert, ein Ja bei Aktiv bedeutet, ".
		"dass der Engel aktiv war und damit ein Anspruch auf ein T-Shirt hat. ".
		"Wenn T-Shirt ein 'Ja' enth&auml;lt, bedeutet dies, dass der Engel ".
		"bereits sein T-Shirt erhalten hat.<br><br>\n";

	echo "<form action=\"./user2.php?action=change\" method=\"POST\">\n";
	echo "<table>\n";
	echo "<input type=\"hidden\" name=\"Type\" value=\"". $_GET["Type"]. "\">\n";

	if( $_GET["Type"] == "Normal" )
	{
		$SQL = "SELECT * FROM User where UID=". $_GET["enterUID"];
		$Erg = mysql_query($SQL, $con);
		
		if (mysql_num_rows($Erg) != 1) 
			echo "<tr><td>Sorry, der Engel (UID=". $_GET["enterUID"]. 
				") wurde in der Liste nicht gefunden.</td></tr>";
		else
		{
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
			echo "  <tr><td>Size</td><td>".
				"<input type=\"text\" size=\"5\" name=\"eSize\" value=\"".
				mysql_result($Erg, 0, "Size")."\"></td></tr>\n";
			echo "  <tr><td>Passwort</td><td>".
				"<input type=\"text\" size=\"40\" name=\"ePasswort\" value=\"".
				mysql_result($Erg, 0, "Passwort")."\" disabled> ".
				"<a href=\"./user2.php?action=newpw&eUID="
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

			// Menu links/rechts 
			echo "  <tr><td>Menu</td><td>\n";
			echo "      <input type=\"radio\" name=\"eMenu\" value=\"L\"";
			if (mysql_result($Erg, 0, "Menu")=='L')
				echo " checked";
			echo ">L \n";
			echo "      <input type=\"radio\" name=\"eMenu\" value=\"R\"";
			if (mysql_result($Erg, 0, "Menu")=='R') 
				echo " checked";
			echo ">R \n";
			echo "</td></tr>\n";
			
			echo "  <tr><td>Hometown</td><td>".
				"<input type=\"text\" size=\"40\" name=\"Hometown\" value=\"".
				mysql_result($Erg, 0, "Hometown")."\"></td></tr>\n";
		} //IF TYPE
	}
	if( $_GET["Type"] == "Secure" )
	{
		// CVS-Rechte
		echo "  <tr><td><br><u>Rights of \"". UID2Nick($_GET["enterUID"]). "\":</u></td></tr>\n";

		$SQL_CVS = "SELECT * FROM `UserCVS` WHERE UID=". $_GET["enterUID"];
		$Erg_CVS =  mysql_query($SQL_CVS, $con);
		
		if( mysql_num_rows($Erg_CVS) != 1) 
			echo "Sorry, der Engel (UID=". $_GET["enterUID"]. ") wurde in der Liste nicht gefunden.";
		else
		{
			$CVS_Data = mysql_fetch_array($Erg_CVS);
			$CVS_Data_i = 1;
			foreach ($CVS_Data as $CVS_Data_Name => $CVS_Data_Value) 
			{
		  		$CVS_Data_i++;
				//nur jeder zweiter sonst wird für jeden text noch die position (Zahl) ausgegeben
				if( $CVS_Data_i%2 && $CVS_Data_Name!="UID") 
				{
				    echo "<tr><td>$CVS_Data_Name</td>\n<td>";
				    echo "<input type=\"radio\" name=\"".($CVS_Data_i-1)."\" value=\"Y\" ";
				    if( $CVS_Data_Value == "Y" )	
				    	echo " checked";
				    echo ">allow \n";
				    echo "<input type=\"radio\" name=\"".($CVS_Data_i-1)."\" value=\"N\" ";
				    if( $CVS_Data_Value == "N" )
				    	echo " checked";
				    echo ">denied \n";
				    echo "</td></tr>";
				} //IF
			} //Foreach	    
			echo "</td></tr>\n";
		} // IF TYPE
	}

	// Ende Formular
	echo "</td></tr>\n";
	echo "</table>\n<br>\n";
	echo "<input type=\"hidden\" name=\"enterUID\" value=\"". $_GET["enterUID"]. "\">\n";
	echo "<input type=\"submit\" value=\"sichern...\">\n";
	echo "</form>";

	if( $_GET["Type"] == "Normal" )
	{
		echo "<form action=\"./user2.php?action=delete\" method=\"POST\">\n";
		echo "<input type=\"hidden\" name=\"enterUID\" value=\"". $_GET["enterUID"]. "\">\n";
		echo "<input type=\"submit\" value=\"l&ouml;schen...\">\n";
		echo "</form>";
	}
}

include ("./inc/footer.php");
?>


