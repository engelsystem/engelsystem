<?php
$title = "R&auml;ume";
$header = "Verwaltung der R&auml;ume";
include ("./inc/header.php");
include ("./inc/funktion_user.php");
include ("./inc/funktion_schichtplan_aray.php");

$Sql = "SELECT * FROM `Room` ORDER BY `Number`, `Name`";
$Erg = mysql_query($Sql, $con);

if( !IsSet($_GET["action"]) )
{
	echo "Hallo ".$_SESSION['Nick'].
		",<br>\nhier hast du die M&ouml;glichkeit, neue R&auml;ume f&uuml;r die Schichtpl&auml;ne einzutragen ".
		"oder vorhandene abzu&auml;ndern:<br><br>\n";

	echo "<a href=\"./room.php?action=new\">- Neuen Raum/Ort eintragen</a><br>\n";
	
	echo "<table width=\"100%\" class=\"border\" cellpadding=\"2\" cellspacing=\"1\">\n";
	echo "<tr class=\"contenttopic\">\n";

	for( $i = 1; $i < mysql_num_fields($Erg); $i++ )
	{
		if( substr( mysql_field_name($Erg, $i), 0, 12) == "DEFAULT_EID_")
			echo "\t<td> Anzahl ". $EngelTypeID[substr( mysql_field_name($Erg, $i), 12)]. "</td>";
		else
			echo "\t<td>". mysql_field_name($Erg, $i)."</td>";
	}
	echo "\t<td>&Auml;ndern</td>";
	echo "</tr>";

	for( $t = 0; $t < mysql_num_rows($Erg); $t++ ) 
	{	
		echo "\t<tr class=\"content\">\n";
		for ($j = 1; $j < mysql_num_fields($Erg); $j++) 
		{
  			echo "\t\t<td>".mysql_result($Erg, $t, $j)."</td>\n";
		}
		echo "\t\t<td><a href=\"./room.php?action=change&RID=".mysql_result($Erg, $t, "RID")."\">###</a></td>\n";
		echo "\t</tr>\n";
	} // ende Auflistung Raeume
	echo "</table>";
} 
else 
{

UnSet($SQL);

switch ($_GET["action"]) {

case 'new':
	echo "Neuen Raum einrichten: <br>";
	echo "<form action=\"./room.php\" method=\"GET\">\n";
	echo "<table>\n";
	
	for( $Uj = 1; $Uj < mysql_num_fields($Erg); $Uj++ )
	{
		if( (mysql_field_name($Erg, $Uj) == "show") || (mysql_field_name($Erg, $Uj) == "FromPentabarf") )
		{
			echo "<tr><td>". mysql_field_name($Erg, $Uj). "</td>".
			     "<td>".
			     "<input type=\"radio\" name=\"". mysql_field_name($Erg, $Uj). "\" value=\"Y\">Yes".
			     "<input type=\"radio\" name=\"". mysql_field_name($Erg, $Uj). "\" value=\"N\">No".
			     "</td></tr>\n";
		}
		else
		{
			//sonderfall fuer Default Engel 
			if( substr( mysql_field_name($Erg, $Uj), 0, 12) == "DEFAULT_EID_")
				$FeldName = "Anzahl ". $EngelTypeID[substr( mysql_field_name($Erg, $Uj), 12)];
			else
				$FeldName = mysql_field_name($Erg, $Uj);
		
			echo "<td>$FeldName</td>".
		        	"<td><input type=\"text\" size=\"40\" name=\"".mysql_field_name($Erg, $Uj)."\">";
			echo "</td></tr>\n";
		}
	}
	echo "</table>\n";
	echo "<input type=\"hidden\" name=\"action\" value=\"newsave\">\n";
	echo "<input type=\"submit\" value=\"sichern...\">\n";
	echo "</form>";
	break;

case 'newsave':
	$vars = $HTTP_GET_VARS;
	$count = count($vars) - 1;
	$vars = array_splice($vars, 0, $count);
	$Keys = "";
	$Values = "";
	foreach($vars as $key => $value)
	{
		$Keys   .= ", `$key`";
		$Values .= ", '$value'";
	}
	$SQL  = "INSERT INTO `Room` (". substr( $Keys, 2). ") VALUES (". substr( $Values, 2). ")";
	SetHeaderGo2Back();
	break;

case 'change':
	if (! IsSet($_GET["RID"])) 
		echo "Fehlerhafter Aufruf!"; 
	else
	{
		$SQL2 = "SELECT * FROM `Room` WHERE `RID`='". $_GET["RID"]. "'";
		$ERG = mysql_query($SQL2, $con);
		
		if( mysql_num_rows( $ERG)>0)
		{
			echo "Raum ab&auml;ndern:\n";
			echo "Hier kannst du eintragen, welche und wieviele Engel f&uuml;r den Raum zur Verfügung stehen m&uuml;ssen.";	
			echo "<form action=\"./room.php\" method=\"GET\">\n";
			echo "<table>\n";
		
			for ($Uj = 1; $Uj < mysql_num_fields($ERG); $Uj++)
			{
				if( (mysql_field_name($ERG, $Uj) == "show") || (mysql_field_name($ERG, $Uj) == "FromPentabarf") )
				{
					echo "<tr><td>". mysql_field_name($Erg, $Uj). "</td>".
					     "<td>".
					     "<input type=\"radio\" name=\"e". mysql_field_name($ERG, $Uj). 
					     	"\" value=\"Y\"". (mysql_result($ERG, 0, $Uj)=='Y'? " checked":""). ">Yes".
					     "<input type=\"radio\" name=\"e". mysql_field_name($ERG, $Uj). 
					     	"\" value=\"N\"". (mysql_result($ERG, 0, $Uj)=='N'? " checked":""). ">No".
					     "</td></tr>\n";
				}
				else
				{
					if( substr( mysql_field_name($ERG, $Uj), 0, 12) == "DEFAULT_EID_")
						//sonderfall fuer Default Engel 
						$FeldName = "Anzahl ". $EngelTypeID[substr( mysql_field_name($ERG, $Uj), 12)];
					else
						$FeldName = mysql_field_name($ERG, $Uj);
					echo "<tr><td>$FeldName</td>".
					     "<td><input type=\"text\" size=\"40\" name=\"e".mysql_field_name($ERG, $Uj)."\" ".
					     "value=\"".mysql_result($ERG, 0, $Uj)."\">".
					     "</td></tr>\n";
				}
			}
			echo "</table>\n";
			echo "<input type=\"hidden\" name=\"eRID\" value=\"". $_GET["RID"]. "\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"changesave\">\n";
			echo "<input type=\"submit\" value=\"sichern...\">\n";
			echo "</form>";
			echo "<form action=\"./room.php\" method=\"GET\">\n";
			echo "<input type=\"hidden\" name=\"RID\" value=\"". $_GET["RID"]. "\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"delete\">\n";
			echo "<input type=\"submit\" value=\"L&ouml;schen...\">";
			echo "</form>";
		}
		else
			echo "FEHLER: Room ID ". $_GET["RID"]. " nicht gefunden";
	}
	break;
	
case 'changesave':
	$sql="";
		$vars = $HTTP_GET_VARS;
		$count = count($vars) - 2;
		$vars = array_splice($vars, 0, $count);
		foreach($vars as $key => $value)
		{
	 		$keys = substr($key,1);
			$sql .= ", `".$keys."`='".$value."' ";
		}
	$SQL = "UPDATE `Room` SET ". substr($sql, 2). " WHERE `RID`='". $_GET["eRID"]. "'";
	SetHeaderGo2Back();
	break;

case 'delete':
	if (IsSet($_GET["RID"])) {
		$SQL="DELETE FROM `Room` WHERE `RID`='". $_GET["RID"]. "'";		
	} else {
		echo "Fehlerhafter Aufruf";
	}
	SetHeaderGo2Back();
	break;

} //switch


// Update ???

if (IsSet($SQL)){ 
//	echo $SQL; 
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

} //IF IsSet($action)

include ("./inc/footer.php");
?>
