<?php
$title = "Schichtplan";
$header = "Neue Schichten erfassen";
$submenus = 1;
include ("../../includes/header.php");

function executeSQL( $SQL)
{
	global $DEBUG, $con;
	
	$Erg = mysql_query($SQL, $con);
	if( $DEBUG ) 
		echo "DEBUG SQL: $SQL<br>\n";
	if ($Erg == 1) 
	{
	     	echo "SQL war erfolgreich";
	}
	else 
	{
		echo "SQL Fehler (". mysql_error($con).")" ;
	}
}

if (!IsSet($_GET["action"])) {
echo "Hallo ".$_SESSION['Nick'].",<br>\n";
echo "hier kannst du Schichten anlegen, &auml;ndern oder l&ouml;schen.<br><br>";
echo "<a href=\"./shiftadd.php\">Neue Schicht einplanen</a><br><br>\n\n";

echo "<form action=\"".$_SERVER['SCRIPT_NAME']."\" method=\"GET\" >\n";
?>
<table width="100%" class="border" cellpadding="2" cellspacing="1">
	<tr class="contenttopic">
		<td></td>
		<td>Datum</td>
		<td>Raum</td>
		<td>Dauer</td>
		<td>&Auml;ndern</td>
	</tr>
<?PHP

$sql =  "SELECT `SID`, `DateS`, `RID`, `Len` FROM `Shifts` ".
	"ORDER BY `RID`, `DateS` ";
$Erg = mysql_query($sql, $con);
$rowcount = mysql_num_rows($Erg);
for( $i = 0; $i < $rowcount; $i++)
{
	echo "\t<tr class=\"content\">\n";
	echo "\t\t<td><input type=\"checkbox\" name=\"SID". mysql_result($Erg, $i, "SID"). "\" ".
		"value=\"". mysql_result($Erg, $i, "SID"). "\"></td>\n";
	echo "\t\t<td>".mysql_result($Erg, $i, "DateS")."</td>\n";
  
	$sql2= "SELECT `Name` FROM `Room` WHERE `RID`='".mysql_result($Erg, $i, "RID")."'";
	$Erg2 = mysql_query($sql2, $con);
	if( mysql_num_rows($Erg2) > 0)
		echo "\t\t<td>".mysql_result($Erg2, 0, "Name")."</td>\n";
	else
		echo "\t\t<td>Unbenkannt (RID=". mysql_result($Erg, $i, "RID"). ")</td>\n";
	echo "\t\t<td>".mysql_result($Erg, $i, "Len")." Std. </td>\n";
	echo "\t\t<td><a href=\"./schichtplan.php?action=change&SID=".
		mysql_result($Erg, $i, "SID")."\">####</a></td>\n";
	echo "\t</tr>\n";
}
echo "</table>\n";

echo "<input type=\"hidden\" name=\"action\" value=\"deleteShifs\">\n";
echo "<input type=\"submit\" value=\"L&ouml;schen...\">\n";
echo "</form>\n";


} else {

// aus sicherheitzgründen wegen späterer genuzung
UnSet($chSQL);

switch ($_GET["action"]){

case 'change':
	if ( !IsSet($_GET["SID"]) )
	{
		echo "Fehlerhafter Aufruf!\n";
	} 
	else 
	{
	
	$sql = "SELECT * FROM `Shifts` WHERE (`SID` = '". $_GET["SID"]. "' )";
	$Erg = mysql_query($sql, $con);

        echo "Schicht ab&auml;ndern: <br>\n";

	// Anzeige Allgemeiner schaischt daten
        echo "<form action=\"".$_SERVER['SCRIPT_NAME']."\" method=\"GET\" >";
        echo "<table>\n";
        echo "  <tr><td>Schichtbeginn</td>".
		"<td><input value=\"". mysql_result($Erg, 0, "DateS"). 
		"\" type=\"text\" size=\"40\" name=\"eDate\"></td></tr>\n";
	echo "  <tr><td>Raum</td><td>\n<select name=\"eRID\">\n";
	
	$sql2 = "SELECT `RID`, `Name` FROM `Room`";
        $Erg2 = mysql_query($sql2, $con);
	$rowcount = mysql_num_rows($Erg2);
	for( $i = 0; $i < $rowcount; $i++ )
	{
		$RID=mysql_result($Erg2, $i, "RID");
		echo "   <option value=\"".$RID."\"";
		if( $RID == mysql_result($Erg, 0, "RID") )
		echo " selected";
		echo ">".mysql_result($Erg2, $i, "Name")."</option>\n";
	}
	echo "  </select>\n</td></tr>\n";
	 
	echo "  <tr><td>Dauer in h</td>".
	 	"<td><input value=\"". mysql_result($Erg, 0, "Len").
		"\" type=\"text\" size=\"40\" name=\"eDauer\"></td></tr>\n";
	echo "  <tr><td>Beschreibung</td>".
	 	"<td><input value=\"". mysql_result($Erg, 0, "Man").
		"\" type=\"text\" size=\"40\" name=\"eName\"></td></tr>\n";
	echo "  <tr><td>URL</td>".
	 	"<td><input value=\"". mysql_result($Erg, 0, "URL").
		"\" type=\"text\" size=\"40\" name=\"eURL\"></td></tr>\n";
        echo "</table>\n";
	 
        echo "<input type=\"hidden\" name=\"SID\" value=\"". $_GET["SID"]. "\">\n";
        echo "<input type=\"hidden\" name=\"action\" value=\"changesave\">\n";
        echo "<input type=\"submit\" value=\"sichern...\">\n";
        echo "</form>\n\n";
         
	// Löschen
	echo "<form action=\"". $_SERVER['SCRIPT_NAME']. "\" method=\"GET\" >\n";
        echo "<input type=\"hidden\" name=\"SID\" value=\"". $_GET["SID"]. "\">\n";
        echo "<input type=\"hidden\" name=\"action\" value=\"delete\">\n";
        echo "<input type=\"submit\" value=\"L&ouml;schen...\">\n";
        echo "</form>\n\n";
	
	echo "<b>ACHTUNG:</b><br>\n";
	echo "Beim L&ouml;schen werden die bisher eingetragenen Engel f&uuml;r diese Schicht mitgel&ouml;scht.<br>\n";

	echo "<br><hr>\n\n\n\n";
	
	//Freie Engelschichten
	$sql3 = "SELECT `TID` FROM `ShiftEntry` WHERE `SID`='". $_GET["SID"]. "' AND `UID`='0'";
	$Erg3 = mysql_query($sql3, $con);
	$rowcount = mysql_num_rows($Erg3);
	
	echo "Folgende Engelschichten sind noch nicht vergeben.\n";
	echo "Und koenen, wenn diese nSchicht nicht benoetigt wird geloet werden:<br>\n";
	for ($j=0; $j < $rowcount; $j++)
	{
		$TID = mysql_result($Erg3, $j, 0);
		echo "<a href=\"./schichtplan.php?action=engelshiftdel&SID=". $_GET["SID"]. "&TID=$TID\">". 
			"freie ". TID2Type($TID). Get_Text("inc_schicht_Engel"). "schicht loeschen</a><br>\n";
	}	
	echo "<br><hr>\n\n\n\n";

	//Ausgabe eingetragener schischten
	$sql3 = "SELECT * FROM `ShiftEntry` WHERE `SID`='". $_GET["SID"]. "' AND NOT `UID`='0'";
	$Erg3 = mysql_query($sql3, $con);
	$rowcount = mysql_num_rows($Erg3);
	 
	echo "Folgende Engel Sind fuer die Schicht eingetargen.\n";
	echo "Und koennen, wenn diese nicht zu Schicht erschienen sind ausgetragen werden:<br>\n";
	echo "<table border=\"1\">\n".
		"<tr class=\"contenttopic\">".
		"<th>nick</th>". 
		"<th>type</th>". 
		"<th>normal</th>". 
		"<th>freeloader :-(</th>". 
		"</tr>";
	
	for ($j=0; $j < $rowcount; $j++)
	{
		$userUID=mysql_result($Erg3, $j, "UID");
		echo "\t<tr>\n";
		echo "\t\t<td>". UID2Nick($userUID). "</td>\n";
		echo "\t\t<td>". TID2Type(mysql_result($Erg3, $j, "TID")). Get_Text("inc_schicht_Engel"). "</td>\n";
		echo "\t\t<td><a href=\"./schichtplan.php?action=engeldel&SID=". $_GET["SID"]. "&UIDs=$userUID&freeloader=0\">###-austragen-###</a></td>\n";
		echo "\t\t<td><a href=\"./schichtplan.php?action=engeldel&SID=". $_GET["SID"]. "&UIDs=$userUID&freeloader=1\">###-austragen-###</a></td>\n";
		echo "\t</tr>\n";
	} // FOR

	echo "</table><br><hr>\n\n\n\n";

	//Nachtragen von Engeln
	echo "Hat ein anderer Engel die Schicht &uuml;bernommen, trage ihn bitte ein:";
	echo "<form action=\"".$_SERVER['SCRIPT_NAME']."\" method=\"GET\" >\n";
	echo "<input type=\"hidden\" name=\"SID\" value=\"". $_GET["SID"]. "\">\n";
        echo "<input type=\"hidden\" name=\"action\" value=\"engeladd\">\n";
	
	// Listet alle Nicks auf
	echo "<select name=\"UIDs\">\n";
	echo "\t<option value=\"0\">--neu--</option>\n";
	
	$usql="SELECT * FROM `User` ORDER BY `Nick`";
	$uErg = mysql_query($usql, $con);
	$urowcount = mysql_num_rows($uErg);
	for ($k=0; $k<$urowcount; $k++)
	{
		echo "\t<option value=\"".mysql_result($uErg, $k, "UID")."\">".
			mysql_result($uErg, $k, "Nick").
			"</option>\n";
	}
	echo "</select>\n";
	
	echo " als \n";
	
	// holt eine liste der benötigten Engel zu dieser Schischt
	$sql3 = "SELECT Count(`TID`) AS `CTID`, `TID` FROM `ShiftEntry` ";
	$sql3.= "WHERE (`SID`='". $_GET["SID"]. "' AND `UID`='0') ";
	$sql3.= "GROUP BY `SID`, `TID`, `UID` ";
	$Erg3 = mysql_query($sql3, $con);
	$i=-1;
	while( ++$i < mysql_num_rows($Erg3))
	{
		$EngelNeed[mysql_result($Erg3, $i, "TID")] = mysql_result($Erg3, $i, "CTID");
	}
	
	// Gibt dei möglich Engeltypen aus und zeigt wíefiel noch beötigt werden
	echo "<select name=\"TID\">\n";
	$SQL2 = "SELECT `TID`, `Name` FROM `EngelType` ORDER BY `Name`";
	$Erg2 = mysql_query($SQL2, $con);
        for ($l = 0; $l < mysql_num_rows($Erg2); $l++) 
	{
		$EngelTID = mysql_result($Erg2, $l, "TID");
		echo "<option value=\"$EngelTID\">";
		echo mysql_result($Erg2, $l, "Name"). Get_Text("inc_schicht_engel");
		if( !isset($EngelNeed[$EngelTID]) )
			echo " (0)";
		else
			echo " (".$EngelNeed[$EngelTID].")";
		echo "</option>\n";
        }
	echo "</select>\n";
	
	echo "<input type=\"submit\" value=\"eintragen...\">\n";
	
	echo "<br>\n<input value=\"1\" type=\"text\" size=\"5\" name=\"eAnzahlNew\"> Anzahl New\n";
	
	echo "</form>";

	} // IF ISSET(
	break;

case 'engeladd':
	if( $_GET["UIDs"]>0)
	{
		
		$SQL = "SELECT * FROM `ShiftEntry` ".
			"WHERE (`SID`='". $_GET["SID"]. "' AND `TID`='". $_GET["TID"]. "' AND `UID`='0')";
		$ERG = mysql_query($SQL, $con);
		if( mysql_num_rows($ERG) != 0 )
		{
			$chSQL  = "UPDATE `ShiftEntry` SET ".
				  "`UID`='". $_GET["UIDs"]. "', `Comment`='shift added by ".$_SESSION['Nick']."' ".
			      "WHERE (`SID`='". $_GET["SID"]. "' AND ".
				  "`TID`='". $_GET["TID"]. "' AND `UID`='0' ) LIMIT 1";
		}
		else
		{
			$chSQL  = "INSERT INTO `ShiftEntry` (`SID`, `TID`, `UID`, `Comment`) VALUES (".
			          "'". $_GET["SID"]. "', '". $_GET["TID"]. "', ".
				      "'". $_GET["UIDs"]. "', 'shift added by ".$_SESSION['Nick']."')";
		}
		echo "Es wird folgende Schicht zus&auml;tzlich eingetragen:<br>\n";
		echo "Engel: ".UID2Nick($_GET["UIDs"])."<br>\n";
		echo "Bemerkung: Schicht eingetragen durch Erzengel ".$_SESSION['Nick']."<br>\n<br>\n";
	}
	else
	{
		echo "Es wird folgende Schicht wurde ". $_GET["eAnzahlNew"]. "x zus&auml;tzlich eingetragen:<br>\n";
		for( $i=0; $i<$_GET["eAnzahlNew"]; $i++)
		{
			echo "$i. <br>\n";
			$SQL  = "INSERT INTO `ShiftEntry` (`SID`, `TID`, `UID`, `Comment`) VALUES (";
			$SQL .= "'". $_GET["SID"]. "', '". $_GET["TID"]. "', '0', NULL)";
			$ERG = mysql_query($SQL, $con);
			if( $DEBUG ) 
				echo "DEBUG SQL: $SQL<br>\n";
			if ($ERG == 1) 
			{
			     	echo "&Auml;nderung wurde gesichert...<br>";
			}
			else 
			{
				echo "Fehler beim speichern... bitte noch ein mal probieren :)<br>";
				echo mysql_error($con);
			}
			echo "Es wird eine weitere Schicht eingetragen:<br><br>\n";
		}
	}
	break;

case 'engeldel':
	$chSQL = "UPDATE `ShiftEntry` SET `UID`='0', `Comment`= 'NULL' WHERE (`SID`='". $_GET["SID"]. 
		 "' AND `UID`='". $_GET["UIDs"]. "') LIMIT 1"; 
	if( isset($_GET["freeloader"]) && $_GET["freeloader"]==1)
	{
		$sql = "SELECT * FROM `Shifts` WHERE (`SID` = '". $_GET["SID"]. "' )";
		$Erg = mysql_query($sql, $con);
		if( mysql_num_rows( $Erg) == 1)
		{
			$UID = $_GET["UIDs"];
	 		$Length = mysql_result($Erg, 0, "Len");
			$Comment = 	"Start: ". mysql_result($Erg, 0, "DateS"). "; ".
					"Beschreibung: ". mysql_result($Erg, 0, "Man"). "; ".
					"Removed by ". $_SESSION['Nick'];
			$ch2SQL = 
				"INSERT INTO `ShiftFreeloader` (`Remove_Time`, `UID`, `Length`, `Comment`) ".
				"VALUES ( CURRENT_TIMESTAMP, '$UID', '$Length', '$Comment');";
		}
	}
	break;

case 'engelshiftdel':
	$chSQL = "DELETE FROM `ShiftEntry` WHERE `SID`='". $_GET["SID"]. "' AND `TID`='". 
			$_GET["TID"]. "' AND `UID`='0' LIMIT 1"; 
	break;

case 'changesave':
	$query = mysql_query("SELECT DATE_ADD('". $_GET["eDate"]. "', INTERVAL '+0 ". $_GET["eDauer"]. "' DAY_HOUR)", $con);
	$enddate = mysql_fetch_row($query);
	
	$chSQL = "UPDATE `Shifts` SET ".
			"`DateS`='". $_GET["eDate"]. "', ".
			"`DateE`='".$enddate[0]. "', ".
			"`RID`='". $_GET["eRID"]. "', ".
			"`Len`='". $_GET["eDauer"]. "', ".
			"`Man`='". $_GET["eName"]. "', ".
			"`URL`='". $_GET["eURL"]. "' ".
			"WHERE `SID`='". $_GET["SID"]. "'";
	SetHeaderGo2Back();
	break;
	
case 'delete':
	$chSQL = "DELETE FROM `Shifts` WHERE `SID`='". $_GET["SID"]. "' LIMIT 1";
	$ch2SQL = "DELETE FROM `ShiftEntry` WHERE `SID`='". $_GET["SID"]. "'";
	SetHeaderGo2Back();
	break;

case 'deleteShifs':
	foreach ($_GET as $k => $v)
		if( strpos( " ".$k, "SID") == 1)
		{
			echo "Shifts $v wird gelöscht...";
			executeSQL( "DELETE FROM `Shifts` WHERE `SID`='$v' LIMIT 1");
			echo "<br>\n";
			echo "ShiftEntry $v wird gelöscht...";
			executeSQL( "DELETE FROM `ShiftEntry` WHERE `SID`='$v'");
			echo "<br><br>\n";
		}
	break;

} // end switch

if (IsSet($chSQL)){
//     echo $chSQL;
	// hier muesste das SQL ausgefuehrt werden...
	$Erg = mysql_query($chSQL, $con);
	if( $DEBUG ) 
		echo "DEBUG SQL: $chSQL<br>\n";
	if ($Erg == 1) 
	{
	     	echo "&Auml;nderung wurde gesichert...<br>";
		if( $DEBUG ) 
			echo "DEBUG: ergebniss". $Erg. "<br>\n";
		if (IsSet($ch2SQL))
		{
			$Erg = mysql_query($ch2SQL, $con);
			if( $DEBUG ) 
				echo "DEBUG SQL: $ch2SQL<br>\n";
			if( $DEBUG ) echo "DEBUG: ergebniss". $Erg. "<br>\n";
		}
	}
	else 
	{
		echo "Fehler beim speichern... bitte noch ein mal probieren :)<br>";
		echo mysql_error($con);
	}
} // Ende Update

}


include ("../../includes/footer.php");
?>
