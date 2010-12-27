<?php
$title = "Himmel";
$header = "Mein Schichtplan";

include ("../../includes/header.php");
include ("../../includes/funktionen.php");
include ("../../includes/funktion_schichtplan.php");
include ("../../includes/funktion_schichtplan_aray.php");


If( !IsSet($_GET["action"]) ) 
{

	echo Get_Text("Hello").$_SESSION['Nick'].", <br>\n";
	echo Get_Text("pub_mywake_beschreibung1")."<br>\n";
	echo Get_Text("pub_mywake_beschreibung2")."<br><br>\n";
	echo Get_Text("pub_mywake_beschreibung3").($LETZTES_AUSTRAGEN).
	     Get_Text("pub_mywake_beschreibung4")."<br><br>\n";

	$USER_ID = $_SESSION['UID'];
	if( $_SESSION['CVS'][ "admin/schichtplan.php" ] == "Y" )
	{
		if( !isset( $_GET["UIDs"]) )
			$_GET["UIDs"] = $_SESSION['UID'];
		else
			$USER_ID = $_GET["UIDs"];
		
		echo "<form action=\"".$_SERVER['SCRIPT_NAME']."\" method=\"GET\" >";

		echo "Liste von <select name=\"UIDs\">\n";
		$usql="SELECT * FROM `User` ORDER BY `Nick`";
		$uErg = mysql_query($usql, $con);
		$urowcount = mysql_num_rows($uErg);
		for ($k=0; $k<$urowcount; $k++)
		{
			echo "\t<option value=\"".mysql_result($uErg, $k, "UID")."\" ".
				( ($_GET["UIDs"]==mysql_result($uErg, $k, "UID")) ? " selected":""). ">".
				mysql_result($uErg, $k, "Nick").
				"</option>\n";
		}
		echo "</select> anzeigen.\n";
		echo mysql_error($con);
		
		echo "<input type=\"submit\" value=\"zeigen\">\n";

		echo "</form>";

	}
	
	$SQL = "SELECT *, `ShiftEntry`.`Comment`, `ShiftEntry`.`TID` FROM `Shifts` ".
	       "INNER JOIN `ShiftEntry` ".
	       "ON `Shifts`.`SID`=`ShiftEntry`.`SID` ".
	       "WHERE `ShiftEntry`.`UID`='$USER_ID' ".
	       "ORDER BY `DateS`";
	$erg = mysql_query($SQL, $con);

	?>
	<table border="0" class="border" cellpadding="2" cellspacing="1">
		<tr>
			<td class="contenttopic"><b><?PHP echo Get_Text("pub_mywake_Datum");?></b></td>
			<td class="contenttopic"><b><?PHP echo Get_Text("pub_mywake_Uhrzeit");?></b></td>
			<td class="contenttopic"><b><?PHP echo Get_Text("pub_mywake_Len");?></b></td>
			<td class="contenttopic"><b><?PHP echo Get_Text("pub_mywake_Name");?></b></td>
			<td class="contenttopic"><b><?PHP echo Get_Text("pub_mywake_Ort");?></b></td>
			<td class="contenttopic"><b><?PHP echo Get_Text("pub_mywake_Bemerkung");?></b></td>
			<td class="contenttopic"><b><?PHP echo Get_Text("pub_mywake_Austragen");?></b></td>
		</tr>
	<?PHP

	for( $i=0; $i<mysql_num_rows( $erg ); $i++ )
	{
		echo "<tr class=\"content\">";

		echo "<td>". substr(mysql_result( $erg, $i, "DateS" ), 0, 10). "</td>\n";
		echo "<td>". substr(mysql_result( $erg, $i, "DateS" ), 11). "</td>\n";
		echo "<td>". mysql_result( $erg, $i, "Len" ). " h</td>\n";
		echo "<td>". mysql_result( $erg, $i, "Man" ). "</td>\n";
		echo "<td>". $RoomID[mysql_result( $erg, $i, "RID" )]. "<br>\n".
		     "(". TID2Type(mysql_result( $erg, $i, "TID" )).Get_Text("inc_schicht_engel"). ")".
		     "</td>\n";
		echo "<td><pre style=\"font-family:Fixedsys,Courier; padding:12x;\">\n". 
		     mysql_result( $erg, $i, "Comment" ). 
		     "\n</pre>\n";
		echo "<br><a href=\"./myschichtplan.php?action=edit&SID=\n".
		     mysql_result( $erg, $i, "SID" )."\"># vi #</a></td>\n";
	
		// bis $LETZTES_AUSTRAGEN (aus dem config-file) soll es moeglich sein, seine Schichten 
		//// selber noch austragen zu koennen...
		$schichtdate = mysql_result( $erg, $i, "DateS" );
		ereg( "^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})", $schichtdate, $res );
		$info["year"]  = $res[1];
		$info["month"] = $res[2];
		$info["day"]   = $res[3];
		$info["hour"]  = $res[4];
		$info["min"]   = $res[5];
		$info["sec"]   = $res[6];
		$schichtdatum = (mktime($info["hour"],$info["min"],$info["sec"],$info["month"],$info["day"],$info["year"]));
		$lastAustragen = $LETZTES_AUSTRAGEN*3600;
		$nowdate = time();
		// wenn Schichtbeginn > jetziges Datum + $LETZTES_AUSTRAGEN - Stunden soll ein Austragen 
		// noch möglich sein
		if( $schichtdatum > ($lastAustragen + $nowdate) ) 
			echo "<td> <a href=\"./myschichtplan.php?action=austragen&SID=".
		             mysql_result( $erg, $i, "SID" )."\">".
		             Get_Text("pub_mywake_austragen")."</a></td>\n";
		else 
			echo "<td>". Get_Text("pub_mywake_austragen_n_c"). "</td>\n";
	
		echo "</tr>\n";
	}
echo "</table>\n\n";

if( $_SESSION['CVS'][ "nonpublic/myschichtplan_ical.php" ] == "Y" )
{
	echo "<br><a href=\"./myschichtplan_ical.php\">". Get_Text("pub_myschichtplan_ical"). "</a>\n";
}

} 
else
{
    If( $_GET["action"] == "austragen" ) 
    {
	echo Get_Text("pub_mywake_delate1")."<br>\n";

	$sql = "SELECT * FROM `Shifts` WHERE (`SID` = '". $_GET["SID"]. "')";
	$Erg = mysql_query($sql, $con);

	$schichtdate = mysql_result( $Erg, 0, "DateS" );
	ereg( "^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})", $schichtdate, $res );
	$info["year"]  = $res[1];
	$info["month"] = $res[2];
	$info["day"]   = $res[3];
	$info["hour"]  = $res[4];
	$info["min"]   = $res[5];
	$info["sec"]   = $res[6];
	$schichtdatum = (mktime($info["hour"],$info["min"],$info["sec"],$info["month"],$info["day"],$info["year"]));
	$lastAustragen = $LETZTES_AUSTRAGEN*3600;
	$nowdate = time();
	// wenn Schichtbeginn > jetziges Datum + $LETZTES_AUSTRAGEN - Stunden soll ein Austragen 
	// noch möglich sein
	if( $schichtdatum > ($lastAustragen + $nowdate) ) 
	{
		$sql2 = "UPDATE `ShiftEntry` ".
			"SET `UID` = '0', `Comment` = NULL ".
		        "WHERE `SID` = '". $_GET["SID"]. "' AND `UID` = '". $_SESSION['UID']. "' LIMIT 1;";
		$Erg2 = mysql_query($sql2, $con);
		if ($Erg2 == 1)
	   		echo Get_Text("pub_mywake_add_ok"). "\n";
		else
			echo Get_Text("pub_mywake_add_ko"). "\n";
	}
	else 
		echo Get_Text("pub_mywake_after"). "\n";
    }
    elseif( $_GET["action"] == "edit" ) 
    {
    	echo Get_Text("pub_myshift_Edit_Text1"). "\n";
	
	$sql = "SELECT * FROM `ShiftEntry` WHERE ";
	$sql.= "(`SID`='". $_GET["SID"]. "' AND `UID`='". $_SESSION['UID']. "')";
	$Erg = mysql_query($sql, $con);

	echo "<form action=\"./myschichtplan.php\" method=\"GET\">\n";
	echo "<textarea name='newtext' cols='50' rows='10'>". mysql_result( $Erg, 0, "Comment" ). "</textarea><br><br>\n";
	echo "<input type=\"submit\" value=\"save\">\n";
	echo "<input type=\"hidden\" name=\"SID\" value=\"". $_GET["SID"]. "\">\n";
	echo "<input type=\"hidden\" name=\"action\" value=\"editSave\">\n";
	echo "</form>";
    }
    elseif( $_GET["action"] == "editSave" ) 
    {
    	echo Get_Text("pub_myshift_EditSave_Text1"). "<br>\n";
	$sql = "UPDATE `ShiftEntry` ".
	       "SET `Comment` = '". $_GET["newtext"]. "' ".
	       "WHERE `SID`='". $_GET["SID"]. "' AND `UID`='". $_SESSION['UID']. "' LIMIT 1;";
	$Erg = mysql_query($sql, $con);
	if ($Erg == 1)
		echo "\t ...". Get_Text("pub_myshift_EditSave_OK"). "\n";
	else
		echo "\t ...". Get_Text("pub_myshift_EditSave_KO"). "\n";
	SetHeaderGo2Back();	
    }
}
include ("../../includes/footer.php");
?>
