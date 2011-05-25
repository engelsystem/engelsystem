<?php
$title = "UserPicture";
$header = "Verwaltung der User Picture";
include ("../../../camp2011/includes/header.php");
include ("../../../camp2011/includes/funktion_schichtplan_aray.php");


if( IsSet($_GET["action"]) )
{
	UnSet($SQL);

	switch ($_GET["action"]) 
	{
		case 'FormUpload':
			echo "Hier kannst Du ein Foto hochladen f&ouml;r:";
			echo "<form action=\"./UserPicture.php?action=sendPicture\" method=\"post\" enctype=\"multipart/form-data\">\n";
			echo "\t<select name=\"UID\">\n";
			$usql="SELECT * FROM `User` ORDER BY `Nick`";
			$uErg = mysql_query($usql, $con);
			for ($k=0; $k<mysql_num_rows($uErg); $k++)
				echo "\t\t<option value=\"".mysql_result($uErg, $k, "UID")."\">". mysql_result($uErg, $k, "Nick"). "</option>\n";
			echo "\t</select>\n";
			echo "\t<input type=\"hidden\" name=\"action\" value=\"sendPicture\">\n";
			echo "\t<input name=\"file\" type=\"file\" size=\"50\" maxlength=\"". get_cfg_var("post_max_size"). "\">\n";
			echo "\t(max ". get_cfg_var("post_max_size"). "Byte)<br>\n";
			echo "\t<input type=\"submit\" value=\"". Get_Text("upload"),"\">\n";
			echo "</form>\n";
			break;
		case 'sendPicture':
		        if( ($_FILES["file"]["size"] > 0) && (isset( $_POST["UID"])) )
		        {
			        if( ($_FILES["file"]["type"] == "image/jpeg") ||
			            ($_FILES["file"]["type"] == "image/png")  ||
			            ($_FILES["file"]["type"] == "image/gif")  )
		        	{
	                		$data = addslashes(fread(fopen($_FILES["file"]["tmp_name"], "r"), filesize($_FILES["file"]["tmp_name"])));

					if( GetPicturShow( $_POST['UID']) == "")
	                		        $SQL = "INSERT INTO `UserPicture` ".
			                                "( `UID`,`Bild`, `ContentType`, `show`) ".
                			                "VALUES ('". $_POST['UID']. "', '$data', '". $_FILES["file"]["type"]. "', 'N')";
		        	        else
                			        $SQL = "UPDATE `UserPicture` SET ".
		                        	        "`Bild`='$data', ".
                		                	"`ContentType`='". $_FILES["file"]["type"]. "' ".
			                                "WHERE `UID`='". $_POST['UID']. "'";

			                	echo "Upload Pictur:'" . $_FILES["file"]["name"] . "', ".
							"MIME-Type: " . $_FILES["file"]["type"]. ", ". 
							$_FILES["file"]["size"]. " Byte ".
							"for ". UID2Nick( $_POST["UID"]);
				}
				else
			                Print_Text("pub_einstellungen_send_KO");
		        }
		        else
                		Print_Text("pub_einstellungen_send_KO");
		        break;

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
			echo "Wollen Sie das Bild von '". UID2Nick( $_GET["UID"]). "' wirklich l&ouml;schen? ".
				"<a href=\"./UserPicture.php?action=delYes&UID=". $_GET["UID"]. "\">Yes</a>";
			break;
		case 'delYes':
			if (IsSet($_GET["UID"]))
			{
				echo "Bild von '". UID2Nick( $_GET["UID"]). "' wurde gel&ouml;scht:<br>";
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
echo "\t<td>L&ouml;schen</td>\n";
echo "</tr>";

for( $t = 0; $t < mysql_num_rows($Erg); $t++ ) 
{	
	$UIDs = mysql_result($Erg, $t, "UID");
	echo "\t<tr class=\"content\">\n";
 		
	echo "\t\t<td>". UID2Nick(mysql_result($Erg, $t, "UID")). "</td>\n";
	echo "\t\t<td>". displayPictur( $UIDs, 0). "</td>\n";
	
	if( GetPicturShow( $UIDs) == "Y")	
		echo "\t\t<td><a href=\"./UserPicture.php?action=SetN&UID=$UIDs\">sperren</a></td>\n";
	elseif( GetPicturShow( $UIDs) == "N")
		echo "\t\t<td><a href=\"./UserPicture.php?action=SetY&UID=$UIDs\">freigeben</a></td>\n";
	else
		echo "\t\t<td>ERROR: show='". GetPicturShow( $UIDs). "'</td>\n";
	echo "\t\t<td><a href=\"./UserPicture.php?action=del&UID=$UIDs\">del</a></td>\n";
	echo "\t</tr>\n";
} // ende Auflistung Raeume
echo "</table>";

echo "<br><a href=\"./UserPicture.php?action=FormUpload\">picture upload</a>\n";

include ("../../../camp2011/includes/footer.php");
?>
