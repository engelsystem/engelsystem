<?PHP

$title = "User-Liste";
$header = "Editieren der Engelliste";
include ("../../includes/header.php");
include ("../../includes/funktion_db_list.php");

if (!IsSet($_GET["enterUID"]))
{
	// Userliste, keine UID uebergeben...

	echo "<a href=\"../makeuser.php\">Neuen Engel eintragen</a><br><br>\n";
	
	if( !isset($_GET["OrderBy"]) ) $_GET["OrderBy"] = "Nick";
	$SQL = "SELECT User.*, UserGroups.Name AS 'Group' FROM `User` ".
		"LEFT JOIN `UserCVS` ON User.UID = UserCVS.UID ".
		"LEFT JOIN `UserGroups` ON UserGroups.UID = UserCVS.GroupID ".
		"ORDER BY `". $_GET["OrderBy"]. "` ASC";
	$Erg = mysql_query($SQL, $con);
	echo mysql_error($con);

	// anzahl zeilen
	$Zeilen  = mysql_num_rows($Erg);

	echo "Anzahl Engel: $Zeilen<br><br>\n";

	?><table width="100%" class="border" cellpadding="2" cellspacing="1"> 
	<tr class="contenttopic">
		<td>
			<a href="<?PHP echo $_SERVER["PHP_SELF"]; ?>?OrderBy=Nick">Nick</a> |
			<a href="<?PHP echo $_SERVER["PHP_SELF"]; ?>?OrderBy=CreateDate">CreateDate</a>
		</td>
		<td><a href="<?PHP echo $_SERVER["PHP_SELF"]; ?>?OrderBy=Name">Name</a></td>
		<td><a href="<?PHP echo $_SERVER["PHP_SELF"]; ?>?OrderBy=Vorname">Vorname</a></td>
		<td><a href="<?PHP echo $_SERVER["PHP_SELF"]; ?>?OrderBy=Alter">Alter</a></td>
		<td>
			<a href="<?PHP echo $_SERVER["PHP_SELF"]; ?>?OrderBy=email">@</a> | 
			<a href="<?PHP echo $_SERVER["PHP_SELF"]; ?>?OrderBy=DECT">DECT</a> | 
			<a href="<?PHP echo $_SERVER["PHP_SELF"]; ?>?OrderBy=Hometown">Hometown</a> | 
			<a href="<?PHP echo $_SERVER["PHP_SELF"]; ?>?OrderBy=lastLogIn">lastLogIn</a> | 
			<a href="<?PHP echo $_SERVER["PHP_SELF"]; ?>?OrderBy=Art">Type</a> | 
			<a href="<?PHP echo $_SERVER["PHP_SELF"]; ?>?OrderBy=ICQ">ICQ</a> |
			<a href="<?PHP echo $_SERVER["PHP_SELF"]; ?>?OrderBy=jabber">jabber</a> |
			<a href="<?PHP echo $_SERVER["PHP_SELF"]; ?>?OrderBy=Group">Group</a> 
		</td>
		<td><a href="<?PHP echo $_SERVER["PHP_SELF"]; ?>?OrderBy=Size">Gr&ouml;&szlig;e</a></td>
		<td><a href="<?PHP echo $_SERVER["PHP_SELF"]; ?>?OrderBy=Gekommen">G</a></td>
		<td><a href="<?PHP echo $_SERVER["PHP_SELF"]; ?>?OrderBy=Aktiv">A</a></td>
		<td><a href="<?PHP echo $_SERVER["PHP_SELF"]; ?>?OrderBy=Tshirt">T</a></td>
		<td>&Auml;nd.</td>
		<td>Secure</td>
	</tr>


	<?PHP
	$Gekommen = 0;
	$Active = 0;
	$Tshirt = 0;
	
	for ($n = 0 ; $n < $Zeilen ; $n++) {
		echo "<tr class=\"content\">\n";
		echo "\t<td>".mysql_result($Erg, $n, "Nick"). "<br>(Create: ". mysql_result($Erg, $n, "CreateDate"). ")</td>\n";
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
			if( strlen( mysql_result($Erg, $n, "Art"))>0)
				echo "\n\t\tType: ". mysql_result($Erg, $n, "Art"). "<br>";
			if( strlen( mysql_result($Erg, $n, "ICQ"))>0)
				echo "\n\t\tICQ: ". mysql_result($Erg, $n, "ICQ"). "<br>";
			if( strlen( mysql_result($Erg, $n, "jabber"))>0)
				echo "\n\t\tjabber: ". mysql_result($Erg, $n, "jabber"). "<br>";
			echo "\n\t\tGroup: ". mysql_result($Erg, $n, "Group"). "<br>";
			echo "</td>\n";
		echo "\t<td>".mysql_result($Erg, $n, "Size")."</td>\n";
		$Gekommen += mysql_result($Erg, $n, "Gekommen");
		echo "\t<td>".mysql_result($Erg, $n, "Gekommen")."</td>\n";
		$Active += mysql_result($Erg, $n, "Aktiv");
		echo "\t<td>".mysql_result($Erg, $n, "Aktiv")."</td>\n";
		$Tshirt += mysql_result($Erg, $n, "Tshirt");
		echo "\t<td>".mysql_result($Erg, $n, "Tshirt")."</td>\n";
		echo "\t<td>". funktion_isLinkAllowed_addLink_OrEmpty( 
				"admin/userChangeNormal.php?enterUID=". 
					mysql_result($Erg, $n, "UID")."&Type=Normal",
				"&Auml;nd.").
			"</td>\n";
		echo "\t<td>". funktion_isLinkAllowed_addLink_OrEmpty( 
				"admin/userChangeSecure.php?enterUID=". 
					mysql_result($Erg, $n, "UID")."&Type=Secure",
				"Secure").
			"</td>\n";
		echo "</tr>\n";
	}
	echo "<tr>".
		"<td></td><td></td><td></td><td></td><td></td><td></td>".
		"<td>$Gekommen</td><td>$Active</td><td>$Tshirt</td><td></td></tr>\n";
	echo "\t</table>\n";
	// Ende Userliste

	echo "<h1>Statistics</h1>";
	funktion_db_element_list_2row( "Hometown",
        	                        "SELECT COUNT(`Hometown`), `Hometown` FROM `User` GROUP BY `Hometown`");

	echo "<br>\n";

	funktion_db_element_list_2row( "Engeltypen",
                                        "SELECT COUNT(`Art`), `Art` FROM `User` GROUP BY `Art`");

	echo "<br>\n";

	funktion_db_element_list_2row( "Used Groups",
		"SELECT UserGroups.Name AS 'GroupName', COUNT(UserGroups.Name) AS Count FROM `UserCVS` ".
		"LEFT JOIN `UserGroups` ON UserGroups.UID = UserCVS.GroupID ".
		"WHERE (UserCVS.GroupID!='NULL') ".
		"GROUP BY `GroupName` ".
		"");
}
else
{
	echo "error";
}

include ("../../includes/footer.php");
?>


