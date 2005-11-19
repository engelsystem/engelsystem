<?php
$title = "R&auml;ume";
$header = "Verwaltung der R&auml;ume";
include ("./inc/header.php");
include ("./inc/funktion_user.php");

function runSQL( $SQL)
{
	include( "./inc/db.php");
	// hier muesste das SQL ausgefuehrt werden...
	$Erg = mysql_query($SQL, $con);
	if ($Erg == 1) {
		echo "&Auml;nderung wurde gesichert...<br>";
		echo "[$SQL]<br>"; 
		return 1;
	} else {
		echo "Fehler beim speichern... bitte noch ein mal probieren :)";
		echo "<br><br>".mysql_error( $con ). "<br>";
		echo "[$SQL]<br>"; 
		return 0;
	}
}


$Sql = "SELECT * FROM `EngelType` ORDER BY NAME";
$Erg = mysql_query($Sql, $con);

if( !IsSet($_GET["action"]) )
{
	echo "Hallo ".$_SESSION['Nick'].
		",<br>\nhier hast du die M&ouml;glichkeit, neue Engeltypen f&uuml;r die Schichtpl&auml;ne einzutragen ".
		"oder vorhandene abzu&auml;ndern:<br><br>\n";

	echo "<a href=\"./EngelType.php?action=new\">- Neuen EngelType eintragen</a><br>\n";
	
	echo "<table width=\"100%\" class=\"border\" cellpadding=\"2\" cellspacing=\"1\">\n";
	echo "<tr class=\"contenttopic\">\n";

	for( $i = 1; $i < mysql_num_fields($Erg); $i++ )
	{
		echo "\t<td>". mysql_field_name($Erg, $i). "</td>";
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
		echo "\t\t<td><a href=\"./EngelType.php?action=change&TID=".mysql_result($Erg, $t, "TID")."\">###</a></td>\n";
		echo "\t</tr>\n";
	} // ende Auflistung Raeume
	echo "</table>";
} 
else 
{

switch ($_GET["action"]) {

case 'new':
	echo "Neuen EngelType einrichten: <br>";
	echo "<form action=\"./EngelType.php\" method=\"GET\">\n";
	echo "<table>\n";
	
	for( $Uj = 1; $Uj < mysql_num_fields($Erg); $Uj++ )
	{
	    echo "<td>".mysql_field_name($Erg, $Uj)."</td>".
	         "<td><input type=\"text\" size=\"40\" name=\"".mysql_field_name($Erg, $Uj)."\"></td></tr>\n";
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
	$Keys="";
	$Values="";
	foreach($vars as $key => $value){
		$Keys   .= ", `$key`";
		$Values .= ", '$value'";
	}
	
	if( runSQL( "INSERT INTO `EngelType` (". substr($Keys, 2). ") VALUES (". substr($Values, 2). ")") )
	{
		SetHeaderGo2Back();
		
		$SQL2 = "SELECT * FROM `EngelType` WHERE `Name`='". $_GET["Name"]. "'";
		$ERG = mysql_query($SQL2, $con);
	
		if( mysql_num_rows($ERG) == 1)
			runSQL( "ALTER TABLE `Room` ADD `DEFAULT_EID_". 
				mysql_result( $ERG, 0, 0). 
				"` INT DEFAULT '0' NOT NULL;");
	}
	break;

case 'change':
	if (! IsSet($_GET["TID"])) {
		echo "Fehlerhafter Aufruf!"; 
	} else {

	echo "Raum ab&auml;ndern:\n";

	echo "Hier kannst du eintragen, den EngelType &auml;ndern.";
	
	echo "<form action=\"./EngelType.php\" method=\"GET\">\n";
	echo "<table>\n";
	
	$SQL2 = "SELECT * FROM `EngelType` WHERE `TID`='". $_GET["TID"]. "'";
	$ERG = mysql_query($SQL2, $con);
        
        for ($Uj = 1; $Uj < mysql_num_fields($ERG); $Uj++)
	{
		echo "<tr><td>". mysql_field_name($ERG, $Uj). "</td>".
		     "<td><input type=\"text\" size=\"40\" name=\"e". mysql_field_name($ERG, $Uj). "\" ".
		     "value=\"". mysql_result($ERG, 0, $Uj). "\"></td></tr>\n";
	}			    
	echo "</table>\n";
	echo "<input type=\"hidden\" name=\"eTID\" value=\"". $_GET["TID"]. "\">\n";
	echo "<input type=\"hidden\" name=\"action\" value=\"changesave\">\n";
	echo "<input type=\"submit\" value=\"sichern...\">\n";
	echo "</form>";
        echo "<form action=\"./EngelType.php\" method=\"GET\">\n";
        echo "<input type=\"hidden\" name=\"TID\" value=\"". $_GET["TID"]. "\">\n";
        echo "<input type=\"hidden\" name=\"action\" value=\"delete\">\n";
        echo "<input type=\"submit\" value=\"L&ouml;schen...\">";
        echo "</form>";
	}
	break;
	
case 'changesave':
        $vars = $HTTP_GET_VARS;
        $count = count($vars) - 2;
        $vars = array_splice($vars, 0, $count);
	$keys="";
	$sql="";
        foreach($vars as $key => $value){
  	      $keys = substr($key,1);
	      $sql .= ", `".$keys."`='".$value."'";
        }
	runSQL( "UPDATE `EngelType` SET ". substr($sql, 2). " WHERE `TID`='". $_GET["eTID"]. "'");
	SetHeaderGo2Back();
	break;

case 'delete':
	if (IsSet($_GET["TID"])) 
	{
		runSQL( "DELETE FROM `EngelType` WHERE `TID`='". $_GET["TID"]. "'");		
		runSQL( "ALTER TABLE `Room` DROP `DEFAULT_EID_". $_GET["TID"]. "`;");
	} else {
		echo "Fehlerhafter Aufruf";
	}
	SetHeaderGo2Back();
	break;
}
}

include ("./inc/footer.php");
?>
