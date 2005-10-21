<?php
$title = "Schicht Hinzufügen";
$header = "Neue Schichten erfassen";

include ("./inc/header.php");
include ("./inc/funktion_user.php");

echo "Hallo ".$_SESSION['Nick'].",<br>\n";

// erstellt ein Array der Reume
	$sql = "SELECT `RID`, `Name` FROM `Room` ORDER BY `Name`";
	$Erg = mysql_query($sql, $con);
	$rowcount = mysql_num_rows($Erg);

	for ($i=0; $i<$rowcount; $i++) 
	{
		$Room[$i]["RID"]  = mysql_result($Erg, $i, "RID");
		$Room[$i]["Name"]	= mysql_result($Erg, $i, "Name");
	}

// erstellt ein Aray der Engeltypen
	$sql = "SELECT `TID`, `Name` FROM `EngelType` ORDER BY `Name`";
	$Erg = mysql_query($sql, $con);
	$rowcount = mysql_num_rows($Erg);

	for ($i=0; $i<$rowcount; $i++) 
	{
		$EngelType[$i]["TID"]  = mysql_result($Erg, $i, "TID");
		$EngelType[$i]["Name"]	= mysql_result($Erg, $i, "Name").Get_Text("inc_schicht_engel");
	}


if (!IsSet($action)) 
	$action = "new";

$Time = time()+3600+3600;

switch ($action){

case 'new':
?>
Hier kannst du neue Schichten eintragen. Dazu musst du den Anfang und das Ende der Schichten eintragen.
&Uuml;ber die L&auml;nge der Schichten errechnet sich dadurch die Anzahl dieser. Dadurch k&ouml;nnen gleich
mehrere Schichten auf einmal erfasst werden:

<form action="<? echo $_SERVER['SCRIPT_NAME']; ?>" >
  <table>
  <tr>
    <td align="right">Name:</td>
    <td><input type="text" name="SchichtName" size="50" value="--???--"></td>
  </tr>
  <tr>
    <td align="right">Ort:</td>
    <td><select name="RID">
	<?
	foreach ($Room As $RTemp) 
		echo "\t<option value=\"". $RTemp["RID"]. "\">". $RTemp["Name"]. "</option>\n";
	?>
    </select></td>
  </tr>
  
  <tr><td><u>Zeit:</u></td></tr>
  <tr>
    <td align="right">Month.Jahr:</td>
    <td><input type="ext" name="MonthJahr" size="7" value="<?echo gmdate("Y-m", $Time)?>"></td>
  </tr>
  <tr>
    <td align="right">Beginn:</td>
    <td>Date<input type="text" name="SDatum" size="5" value="<?echo gmdate("d", $Time)?>">
        Time<input type="text" name="STime" size="5" value="10"></td>
  </tr>
  <tr>
    <td align="right">More then One</td>
    <td><input type="checkbox" name="MoreThenOne" value="ON" checked></td>
  </tr>
  <tr>
    <td align="right">End:</td>
    <td>Date<input type="text" name="EDatum" size="5" value="<?echo gmdate("d", $Time)?>">
        Time<input type="text" name="ETime" size="5" value="12"></td>
  </tr>
  <tr>
    <td align="right">L&auml;nge in h:</td>
    <td><input type="text" name="len" size="5" value="2"></td>
  </tr>
  <tr>
    <td align="right">Sonderschichten ein:</td>
    <td><input type="checkbox" name="NachtON" value="ON"></td>
  </tr>
  <tr>
    <td align="right">Sonder in h (Time;Time):</td>
    <td><input type="text" name="len_night" size="50" value="0;4;8;10;12;14;16;18;20;22;24"></td>
  </tr>
  
  <tr><td><u>Anzahl Engel je Type:</u></td></tr>
<?
	foreach ($EngelType As $TTemp)
	{
		echo "  <tr><td align=\"right\">". $TTemp["Name"]. ":</td>\n";
		echo "      <td><input type=\"text\" name=\"EngelType". $TTemp["TID"]. "\" size=\"5\" value=\"0\"></td>\n";
	}
?>
</table>
 <br>
<input type="hidden" name="OnlyShow" value="ON">
<input type="hidden" name="action" value="newsave">
<input type="submit" value="zeig mal Gabriel!">
</form>

<?
	break; // Ende new

case 'newsave':
    if (isset($SDatum) && ($len > 0)) {
	$lenOrg = $len;
	if( $NachtON == "ON" )
	{	
		$lenArrayDummy = explode( ";", $len_night);
                foreach ( $lenArrayDummy as $Temp )
                {
			if( isset($Temp2) )
			{
				$lenArray[$Temp2] = $Temp-$Temp2;
			}
			$Temp2 = $Temp;
			
        	}//foreach
	}//IF( $NachtON == "ON" )

	echo "<table border=\"1\">\n";
	echo "<tr>\n";
	echo "\t<td valign=\"top\" align=\"center\">Start</td>\n";
	echo "\t<td valign=\"top\" align=\"center\">End</td>\n";
	echo "\t<td valign=\"top\" align=\"center\">len</td>\n";
	echo "\t<td valign=\"top\" align=\"center\">RID</td>\n";
	echo "\t<td valign=\"top\" align=\"center\">Beschreibung</td>\n";
	echo "\t<td valign=\"top\" align=\"center\">Entry 'Shifts'</td>\n";
	echo "\t<td valign=\"top\" align=\"center\">SID</td>\n";
	echo "\t<td valign=\"top\" align=\"center\">Entrys</td>\n";
	echo "</tr>\n";
 	
	$DateEnd = $SDatum;
 	$TimeEnd = $STime;
	do {	
		// define Start time
		$Date = $DateEnd;
		$Time = $TimeEnd;
		$_DateS = $MonthJahr. "-". $Date. " ". $Time. ":00:00";
			
		// define End time
	 	if( $NachtON == "ON" )
		{
			$len = $lenArray[$Time];
		}
		$TimeEnd = $Time+ $len;
		
		//Tagesüberschreitung
		while( $TimeEnd >= 24 )
		{
			$TimeEnd -= 24;
			$DateEnd += 1;
		}
		//ist schischt zu lang dan verkürzen	
		if( $DateEnd > $EDatum || ($DateEnd == $EDatum && $TimeEnd >= $ETime) ) 
		{
			$len -= ($DateEnd- $EDatum)*24; 
			$len -= ($TimeEnd- $ETime);		// -(-) ->> +
			$DateEnd = $EDatum;
			$TimeEnd = $ETime;
		}
		$_DateE = $MonthJahr. "-". $DateEnd. " ". $TimeEnd. ":00:00";

		if( $_DateS != $_DateE )
			CreateNewEntry();
		
		if( $MoreThenOne!="ON" ) break;
		if( $DateEnd == $EDatum && $TimeEnd >= $ETime ) break;
	} while( true );
	echo "</table>";
	
	if( $OnlyShow!="" ) 
	{
		echo "<form action=\"". $_SERVER['SCRIPT_NAME']. "\">";
		echo "\n\t<Input type=\"hidden\" name=\"SchichtName\" value=\"$SchichtName\">";
		echo "\n\t<input type=\"hidden\" name=\"MonthJahr\" value=\"$MonthJahr\">";
		echo "\n\t<input type=\"hidden\" name=\"SDatum\" value=\"$SDatum\">";
		echo "\n\t<input type=\"hidden\" name=\"STime\" value=\"$STime\">";
		echo "\n\t<input type=\"hidden\" name=\"MoreThenOne\" value=\"$MoreThenOne\">";
		echo "\n\t<input type=\"hidden\" name=\"EDatum\" value=\"$EDatum\">";
		echo "\n\t<input type=\"hidden\" name=\"ETime\" value=\"$ETime\">";
		echo "\n\t<input type=\"hidden\" name=\"len\" value=\"$lenOrg\">";
		echo "\n\t<input type=\"hidden\" name=\"RID\" value=\"$RID\">";
		echo "\n\t<input type=\"hidden\" name=\"NachtON\" value=\"$NachtON\">";
		echo "\n\t<input type=\"hidden\" name=\"len_night\" value=\"$len_night\">";
		echo "\n\t<input type=\"hidden\" name=\"OnlyShow\" value=\"\">";
		foreach ($EngelType As $TTemp)
		{
			$Temp = "EngelType".$TTemp["TID"];
			echo "\n\t<input type=\"hidden\" name=\"". $Temp. "\" value=\"".$$Temp."\">";
		}	
		echo "\n\t<input type=\"hidden\" name=\"action\" value=\"newsave\">";
		echo "\n\t<input type=\"submit\" value=\"mach mal Gabriel!\">";
		echo "\n</form>";
	} //if
    } //IF
    break;

case 'engeldel':
	break;

} // end switch



function CreateNewEntry() 
{
	global $con, $_DateS, $_DateE, $len, $RID, $SchichtName, $OnlyShow, $EngelType, $DEBUG;
	foreach ($EngelType As $TTemp)
	{
		$Temp = "EngelType".$TTemp["TID"];
		global $$Temp;
	}	

	echo "<tr>\n";

	echo "\t<td>$_DateS</td>\n";
	echo "\t<td>$_DateE</td>\n";
	echo "\t<td>$len</td\n>";
	echo "\t<td>$RID</td>\n";
	echo "\t<td>$SchichtName</td>\n";
	
	
	// Ist eintarg schon vorhanden?	
	$SQL  = "SELECT SID FROM `Shifts` ";
	$SQL .=	"WHERE (".
		"`DateS` = '". $_DateS. "' AND ".
		"`DateE` = '". $_DateE. "' AND ".
		"`RID` = '". $RID. "');";
	$Erg = mysql_query($SQL, $con);
	
	if( mysql_num_rows($Erg) != 0 )
		echo "\t<td>exists</td>";
	elseif( $OnlyShow == "" )   
	{
		//Suchet nach letzter SID
		$SQLin = "SELECT `SID` FROM `Shifts` ".
			 "WHERE NOT (`FromPentabarf` = 'Y') ".
			 "ORDER BY `SID` DESC";
		$Ergin = mysql_query($SQLin, $con);
		if( mysql_num_rows( $Ergin) > 0)
			$newSID = mysql_result( $Ergin, 0, 0)+1;
		else
			$newSID = 10000;
		
		// erstellt Eintrag in Shifts für die algemeine schicht
		$SQL  = "INSERT INTO `Shifts` (`SID`, `DateS`, `DateE`, `Len`, `RID`, `Man`) VALUES ('$newSID', ";
		$SQL .= "'". $_DateS. "', '". $_DateE. "', ";
		$SQL .= "'". $len. "', '". $RID. "', ";
		$SQL .= "'". $SchichtName. "');";
		$Erg = mysql_query($SQL, $con);

		$SQLFail = "\n\t<br>[".$SQL. "]";

		if ($Erg == 1) echo "\t<td>pass</td>\n";
		else           echo "\t<td>fail <br>\n<u>". mysql_error($con). "</u>$SQLFail</td>\n";

	} else
		echo "\t<td>only show</td>\n";
		
	// sucht SID von eingetragennen schiten
	$SQL  = "SELECT SID FROM `Shifts` ";
	$SQL .=	"WHERE (".
		"`DateS` = '". $_DateS. "' AND ".
		"`DateE` = '". $_DateE. "' AND ".
		"`Len` = '". $len. "' AND ".
		"`RID` = '". $RID. "');";
	$Erg = mysql_query($SQL, $con);
	if( mysql_num_rows($Erg) == 0 )
		echo "\t<td>?</td>";
	else	
	{
		$SID = mysql_result($Erg, 0, "SID");
		echo "\t<td>$SID</td>";
	}

	// erstellt für jeden Engeltypen die eintrage in 'ShiftEntry'
	echo "\t<td>";
	foreach ($EngelType As $TTemp)
	{
		$Temp = "EngelType".$TTemp["TID"];
		
		if( $$Temp > 0 )
		{
			$i = 0;
			echo $$Temp. " ".$TTemp["Name"]. "<br>\t";
			while( $i++ < $$Temp )
			{
				$SQL  = "INSERT INTO `ShiftEntry` (`SID`, `TID`) VALUES (";
				$SQL .= "'$SID', ";
				$SQL .= "'". $TTemp["TID"]. "');";

				if( $OnlyShow == "" )
				{
					$Erg = mysql_query($SQL, $con);

					if( $DEBUG ) $SQLFail = "\n\t<br>[".$SQL. "]";

					if ($Erg == 1) echo "'pass' ";
					else           echo "'fail' <u>". mysql_error($con). "</u>$SQLFail</td>\n";

				}
				else
					echo "'only show' ";
			}
			echo "<br>";
		} // IF $$TEMP
	} // FOREACH
	echo "</td>";

	
	echo "</tr>\n";
}

include ("./inc/footer.php");
?>
