<?php
$title = "UserPicture";
$header = "Verwaltung der User Picture";
include ("./inc/header.php");
include ("./inc/funktion_user.php");
include ("./inc/funktion_schichtplan_aray.php");


if( IsSet($_GET["action"]) )
{
	UnSet($SQL);

	switch ($_GET["action"]) 
	{
		case 'SetN':
			if (IsSet($_GET["UID"]))
			{
				echo "Bild von '". UID2Nick( $_GET["UID"]). "' wurde gesperrt:<br>";
				$SQL = "UPDATE `UserPicture` SET `show`='N' WHERE `UID`='". $_GET["UID"]. "'";
			}
			else
				echo "Fehlerhafter Aufruf";
			break;
		case 'SetY':
			if (IsSet($_GET["UID"]))
			{
				echo "Bild von '". UID2Nick( $_GET["UID"]). "' wurde Freigegeben:<br>";
				$SQL = "UPDATE `UserPicture` SET `show`='Y' WHERE `UID`='". $_GET["UID"]. "'";
			}
			else
				echo "Fehlerhafter Aufruf";
			break;
		case 'del':
			echo "Wollen Sie das Bild von '". UID2Nick( $_GET["UID"]). "' wirklich löschen? ".
				"<a href=\"./UserPicture.php?action=delYes&UID=". $_GET["UID"]. "\">Yes</a>";
			break;
		case 'delYes':
			if (IsSet($_GET["UID"]))
			{
				echo "Bild von '". UID2Nick( $_GET["UID"]). "' wurde gelöscht:<br>";
				$SQL = "DELETE FROM `UserPicture` WHERE `UID`='". $_GET["UID"]. "' LIMIT 1";
			}
			else
				echo "Fehlerhafter Aufruf";
			break;
		default:	
			echo "Fehlerhafter Aufruf";
			
	} //switch

	// Update ???
	if (IsSet($SQL))
	{ 
		// hier muesste das SQL ausgefuehrt werden...
		$Erg = mysql_query($SQL, $con);
		if ($Erg == 1) 
		     echo "&Auml;nderung wurde gesichert...<br>";
		else
		{
		     echo "Fehler beim speichern... bitte noch ein mal probieren :)";
		     echo "<br><br>".mysql_error( $con ). "<br>($SQL)<br>";
		}
	} // Ende Update								
	echo "<br>\n<hr width=\"100%\">\n<br>\n\n";
} //IF IsSet($action)


//ausgabe der Liste
$Sql = "SELECT * FROM `UserPicture` WHERE `UID`>0;";
$Erg = mysql_query($Sql, $con);
	
echo "Hallo ". $_SESSION['Nick']. ",<br>\nhier hast du die M&ouml;glichkeit, die Bilder der Engel freizugeben:<br><br>\n";

echo "<table width=\"100%\" class=\"border\" cellpadding=\"2\" cellspacing=\"1\">\n";
echo "<tr class=\"contenttopic\">\n";
echo "\t<td>User</td>\n";
echo "\t<td>Bild</td>\n";
echo "\t<td>Status</td>\n";
echo "\t<td>Löschen</td>\n";
echo "</tr>";

for( $t = 0; $t < mysql_num_rows($Erg); $t++ ) 
{	
	$UID = mysql_result($Erg, $t, "UID");
	echo "\t<tr class=\"content\">\n";
 		
	echo "\t\t<td>". UID2Nick(mysql_result($Erg, $t, "UID")). "</td>\n";
	echo "\t\t<td>". displayPictur( $UID, 0). "</td>\n";
	
	if( GetPicturShow( $UID) == "Y")	
		echo "\t\t<td><a href=\"./UserPicture.php?action=SetN&UID=$UID\">sperren</a></td>\n";
	elseif( GetPicturShow( $UID) == "N")
		echo "\t\t<td><a href=\"./UserPicture.php?action=SetY&UID=$UID\">freigeben</a></td>\n";
	else
		echo "\t\t<td>ERROR: show='". GetPicturShow( $UID). "'</td>\n";
	echo "\t\t<td><a href=\"./UserPicture.php?action=del&UID=$UID\">del</a></td>\n";
	echo "\t</tr>\n";
} // ende Auflistung Raeume
echo "</table>";

include ("./inc/footer.php");
?>
