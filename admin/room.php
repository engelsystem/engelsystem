<?php
$title = "R&auml;ume";
$header = "Verwaltung der R&auml;ume";
include ("./inc/header.php");
include ("./inc/funktion_user.php");
include ("./inc/funktion_schichtplan.php");

$Sql = "SELECT * FROM `Room` ORDER BY Number, Name";
$Erg = mysql_query($Sql, $con);

if( !IsSet($action) )
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

switch ($action) {

case 'new':
	echo "Neuen Raum einrichten: <br>";
	echo "<form action=\"./room.php\" method=\"POST\">\n";
	echo "<table>\n";
	
	for( $Uj = 1; $Uj < mysql_num_fields($Erg); $Uj++ )
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
	echo "</table>\n";
	echo "<input type=\"hidden\" name=\"action\" value=\"newsave\">\n";
	echo "<input type=\"submit\" value=\"sichern...\">\n";
	echo "</form>";
	break;

case 'newsave':
	$vars = $HTTP_POST_VARS;
	$count = count($vars) - 1;
	$vars = array_splice($vars, 0, $count);
	foreach($vars as $key => $value){
		$Keys   .= ", `$key`";
		$Values .= ", '$value'";
	}
	
	//ermitteln des letzten eintrages
	$SQLin = "SELECT `RID` FROM `Room` ".
		 "WHERE NOT (`FromPentabarf` = 'Y') ".
		 "ORDER BY `RID` DESC";
	$Ergin = mysql_query($SQLin, $con);
	if( mysql_num_rows( $Ergin) > 0)
		$RID = mysql_result( $Ergin, 0, 0)+1;
	else
		$RID = 10000;
	
	$SQL  = "INSERT INTO `Room` (`RID`$Keys) VALUES ( '$RID'$Values)";
	SetHeaderGo2Back();
	break;

case 'change':
	if (! IsSet($RID)) {
		echo "Fehlerhafter Aufruf!"; 
	} else {

	echo "Raum ab&auml;ndern:\n";

	echo "Hier kannst du eintragen, welche und wieviele Engel f&uuml;r den Raum zur Verfügung stehen m&uuml;ssen.";
	
	echo "<form action=\"./room.php\" method=\"POST\">\n";
	echo "<table>\n";
	
	$SQL2 = "SELECT * FROM `Room` WHERE `RID`='$RID'";
	$ERG = mysql_query($SQL2, $con);
        
        for ($Uj = 1; $Uj < mysql_num_fields($ERG); $Uj++)
	{
		//sonderfall fuer Default Engel 
		if( substr( mysql_field_name($ERG, $Uj), 0, 12) == "DEFAULT_EID_")
			$FeldName = "Anzahl ". $EngelTypeID[substr( mysql_field_name($ERG, $Uj), 12)];
		else
			$FeldName = mysql_field_name($ERG, $Uj);
		
		echo "<tr><td>$FeldName</td>".
		     "<td><input type=\"text\" size=\"40\" name=\"e".mysql_field_name($ERG, $Uj)."\" ".
		     "value=\"".mysql_result($ERG, 0, $Uj)."\">";
		echo"</td></tr>\n";
	}			    
	echo "</table>\n";
	echo "<input type=\"hidden\" name=\"eRID\" value=\"$RID\">\n";
	echo "<input type=\"hidden\" name=\"action\" value=\"changesave\">\n";
	echo "<input type=\"submit\" value=\"sichern...\">\n";
	echo "</form>";
        echo "<form action=\"./room.php\" method=\"POST\">\n";
        echo "<input type=\"hidden\" name=\"RID\" value=\"$RID\">\n";
        echo "<input type=\"hidden\" name=\"action\" value=\"delete\">\n";
        echo "<input type=\"submit\" value=\"L&ouml;schen...\">";
        echo "</form>";
	}
	break;
	
case 'changesave':
	$sql="";
        $vars = $HTTP_POST_VARS;
        $count = count($vars) - 2;
        $vars = array_splice($vars, 0, $count);
        foreach($vars as $key => $value){
 		$keys = substr($key,1);
		$sql .= ", `".$keys."`='".$value."' ";
	       
        }
	$SQL = "UPDATE `Room` SET ". substr($sql, 2). " WHERE `RID`='".$eRID."'";
	SetHeaderGo2Back();
	break;

case 'delete':
	if (IsSet($RID)) {
		$SQL="DELETE FROM `Room` WHERE `RID`='$RID'";		
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
	if ($Erg == 1) {
	     echo "&Auml;nderung wurde gesichert...<br>";
	} else {
	     echo "Fehler beim speichern... bitte noch ein mal probieren :)";
	     echo "<br><br>".mysql_error( $con ). "<br>($SQL)<br>";
	}
} // Ende Update								

} //IF IsSet($action)

include ("./inc/footer.php");
?>
