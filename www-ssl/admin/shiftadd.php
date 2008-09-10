<?php
$title = "Schicht Hinzufügen";
$header = "Neue Schichten erfassen";

include ("../../includes/header.php");

$Time = time()+3600+3600;

echo "Hallo ".$_SESSION['Nick'].",<br>\n";

// erstellt ein Array der Reume
	$sql = "SELECT `RID`, `Name` FROM `Room` ORDER BY `Name`";
	$Erg = mysql_query($sql, $con);
	$rowcount = mysql_num_rows($Erg);

	for ($i=0; $i<$rowcount; $i++) 
	{
		$Room[$i]["RID"]  = mysql_result($Erg, $i, "RID");
		$Room[$i]["Name"] = mysql_result($Erg, $i, "Name");
	}

// erstellt ein Aray der Engeltypen
	$sql = "SELECT `TID`, `Name` FROM `EngelType` ORDER BY `Name`";
	$Erg = mysql_query($sql, $con);
	$rowcount = mysql_num_rows($Erg);

	for ($i=0; $i<$rowcount; $i++) 
	{
		$EngelType[$i]["TID"]  = mysql_result($Erg, $i, "TID");
		$EngelType[$i]["Name"] = mysql_result($Erg, $i, "Name").Get_Text("inc_schicht_engel");
	}

// sesion mit stanadrt werten befüllen
if( !isset( $_SESSION['shiftadd.php']['SchichtName']))
{
	$_SESSION['shiftadd.php']['SchichtName'] = "--???--";
	$_SESSION['shiftadd.php']['RID'] = "";
	$_SESSION['shiftadd.php']['MonthJahr'] = gmdate("Y-m", $Time);
	$_SESSION['shiftadd.php']['SDatum'] = gmdate("d", $Time);
	$_SESSION['shiftadd.php']['STime'] = "10";
	$_SESSION['shiftadd.php']['MoreThenOne'] = "ON";
	$_SESSION['shiftadd.php']['EDatum'] = gmdate("d", $Time);
	$_SESSION['shiftadd.php']['ETime'] = "12";
	$_SESSION['shiftadd.php']['len'] = "2";
	$_SESSION['shiftadd.php']['NachtON'] = "OFF";
	$_SESSION['shiftadd.php']['len_night'] = "00-04-08-10-12-14-16-18-20-22-24";
}
// wenn werte übergeben in sesion eintragen
if( !isset($_GET["NachtON"]))
	$_GET["NachtON"] = "OFF";
if( !isset($_GET["MoreThenOne"]))
	$_GET["MoreThenOne"] = "OFF";
if( isset( $_GET["SchichtName"]))
{
	foreach ($_GET as $k => $v)
	{
		$_SESSION['shiftadd.php'][$k] = $v;
	}
}


if (!IsSet($_GET["action"])) 
	$_GET["action"] = "new";

switch( $_GET["action"])
{
case 'new':
?>
Hier kannst du neue Schichten eintragen. Dazu musst du den Anfang und das Ende der Schichten eintragen.
&Uuml;ber die L&auml;nge der Schichten errechnet sich dadurch die Anzahl dieser. Dadurch k&ouml;nnen gleich
mehrere Schichten auf einmal erfasst werden:

<form action="<?PHP echo $_SERVER['SCRIPT_NAME']; ?>" >
  <table>
  <tr>
    <td align="right">Name:</td>
    <td><input type="text" name="SchichtName" size="50" value="<?PHP echo $_SESSION["shiftadd.php"]["SchichtName"]; ?>"></td>
  </tr>
  <tr>
    <td align="right">Ort:</td>
    <td><select name="RID">
<?PHP
	foreach ($Room As $RTemp)
	{
		echo "\t<option value=\"". $RTemp["RID"]. "\"";
		if( $RTemp["RID"] == $_SESSION["shiftadd.php"]["RID"])
			echo " SELECTED";
		echo ">". $RTemp["Name"]. "</option>\n";
	}
	?>
    </select></td>
  </tr>
  
  <tr><td><u>Zeit:</u></td></tr>
  <tr>
    <td align="right">Month.Jahr:</td>
    <td><input type="ext" name="MonthJahr" size="7" value="<?PHP echo $_SESSION["shiftadd.php"]["MonthJahr"]; ?>"></td>
  </tr>
  <tr>
    <td align="right">Beginn:</td>
    <td>Date<input type="text" name="SDatum" size="5" value="<?PHP echo $_SESSION["shiftadd.php"]["SDatum"]; ?>">
        Time<input type="text" name="STime" size="5" value="<?PHP echo $_SESSION["shiftadd.php"]["STime"]; ?>"></td>
  </tr>
  <tr>
    <td align="right">More then One</td>
    <td><input type="checkbox" name="MoreThenOne" value="ON" <?PHP 
   	if( $_SESSION["shiftadd.php"]["MoreThenOne"]=="ON")
		echo " CHECKED";
	?>></td>
  </tr>
  <tr>
    <td align="right">End:</td>
    <td>Date<input type="text" name="EDatum" size="5" value="<?PHP echo $_SESSION["shiftadd.php"]["EDatum"]; ?>">
        Time<input type="text" name="ETime" size="5" value="<?PHP echo $_SESSION["shiftadd.php"]["ETime"]; ?>"></td>
  </tr>
  <tr>
    <td align="right">L&auml;nge in h:</td>
    <td><input type="text" name="len" size="5" value="<?PHP echo $_SESSION["shiftadd.php"]["len"]; ?>"></td>
  </tr>
  <tr>
    <td align="right">Sonderschichten ein:</td>
    <td><input type="checkbox" name="NachtON" value="ON" <?PHP 
    	if($_SESSION["shiftadd.php"]["NachtON"]=="ON")
		echo " CHECKED";
	?>></td>
  </tr>
  <tr>
    <td align="right">Sonder in h (Time;Time):</td>
    <td><input type="text" name="len_night" size="50" value="<?PHP echo $_SESSION["shiftadd.php"]["len_night"]; ?>"></td>
  </tr>
  
  <tr><td><u>Anzahl Engel je Type:</u></td></tr>
<?PHP
	foreach ($EngelType As $TTemp)
	{
		echo "  <tr><td align=\"right\">". $TTemp["Name"]. ":</td>\n";
		echo "      <td><input type=\"text\" name=\"EngelType". $TTemp["TID"]. "\" size=\"5\" value=\"";
		if( isset($_SESSION["shiftadd.php"][ "EngelType". $TTemp["TID"] ]))
			echo $_SESSION["shiftadd.php"][ "EngelType". $TTemp["TID"] ];
		else
			echo "0";
		echo "\"></td>\n";
	}
?>
</table>
 <br>
<input type="hidden" name="OnlyShow" value="ON">
<input type="hidden" name="action" value="newsave">
<input type="submit" value="zeig mal Gabriel!">
</form>

<?PHP
	break; // Ende new

case 'newsave':
    if (isset($_GET["SDatum"]) && ($_GET["len"] > 0))
    {
	$lenOrg = $_GET["len"];
	if( $_GET["NachtON"] == "ON" )
	{	
		$lenArrayDummy = explode( "-", $_GET["len_night"]);
                foreach ( $lenArrayDummy as $Temp )
                {
			if( isset($Temp2) )
				$lenArray[intval($Temp2)] = intval($Temp)-intval($Temp2);
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
 	
	$DateEnd = $_GET["SDatum"];
 	$TimeEnd = intval($_GET["STime"]);
	$len=0;
	do
	{	
		// define Start time
		$Date = $DateEnd;
		$Time = $TimeEnd;
		$_DateS = $_GET["MonthJahr"]. "-". $Date. " ". $Time. ":00:00";
			
		// define End time
	 	if( $_GET["NachtON"] == "ON" )
		{
			if( !isset($lenArray[$Time])) die("Zeit $Time h nicht definiert.");
			$_GET["len"] = $lenArray[$Time];
			if( $_GET["len"]<1) die("len <1");
		}
		$TimeEnd = $Time+ $_GET["len"];
		
		//Tagesüberschreitung
		while( $TimeEnd >= 24 )
		{
			$TimeEnd -= 24;
			$DateEnd += 1;
		}
		//ist schischt zu lang dan verkürzen
		if( $DateEnd > $_GET["EDatum"] || ($DateEnd == $_GET["EDatum"] && $TimeEnd >= $_GET["ETime"]) ) 
		{
			$_GET["len"] -= ($DateEnd- $_GET["EDatum"])*24; 
			$_GET["len"] -= ($TimeEnd- $_GET["ETime"]);		// -(-) ->> +
			$DateEnd = $_GET["EDatum"];
			$TimeEnd = $_GET["ETime"];
		}
		$_DateE = $_GET["MonthJahr"]. "-". $DateEnd. " ". $TimeEnd. ":00:00";

		if( $_DateS != $_DateE )
			CreateNewEntry();
		
		if( $_GET["MoreThenOne"]!="ON" ) break;
		if( $DateEnd >= $_GET["EDatum"] && $TimeEnd >= intval($_GET["ETime"]) ) break;
	} while( true );
	echo "</table>";
	
	if( $_GET["OnlyShow"]=="ON" ) 
	{
		echo "<form action=\"". $_SERVER['SCRIPT_NAME']. "\">";
		echo "\n\t<Input type=\"hidden\" name=\"SchichtName\" value=\"". $_GET["SchichtName"]. "\">";
		echo "\n\t<input type=\"hidden\" name=\"MonthJahr\" value=\"". $_GET["MonthJahr"]. "\">";
		echo "\n\t<input type=\"hidden\" name=\"SDatum\" value=\"". $_GET["SDatum"]. "\">";
		echo "\n\t<input type=\"hidden\" name=\"STime\" value=\"". $_GET["STime"]. "\">";
		echo "\n\t<input type=\"hidden\" name=\"MoreThenOne\" value=\"". $_GET["MoreThenOne"]. "\">";
		echo "\n\t<input type=\"hidden\" name=\"EDatum\" value=\"". $_GET["EDatum"]. "\">";
		echo "\n\t<input type=\"hidden\" name=\"ETime\" value=\"". $_GET["ETime"]. "\">";
		echo "\n\t<input type=\"hidden\" name=\"len\" value=\"". $lenOrg. "\">";
		echo "\n\t<input type=\"hidden\" name=\"RID\" value=\"". $_GET["RID"]. "\">";
		echo "\n\t<input type=\"hidden\" name=\"NachtON\" value=\"". $_GET["NachtON"]. "\">";
		echo "\n\t<input type=\"hidden\" name=\"len_night\" value=\"". $_GET["len_night"]. "\">";
		echo "\n\t<input type=\"hidden\" name=\"OnlyShow\" value=\"OFF\">";
		foreach ($EngelType As $TTemp)
		{
			$Temp = "EngelType".$TTemp["TID"];
			echo "\n\t<input type=\"hidden\" name=\"". $Temp. "\" value=\"". $_GET[$Temp]. "\">";
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
	global $con, $_DateS, $_DateE, $EngelType, $DEBUG;
	foreach ($EngelType As $TTemp)
	{
		$Temp = "EngelType".$TTemp["TID"];
		global $$Temp;
	}	

	echo "<tr>\n";

	echo "\t<td>$_DateS</td>\n";
	echo "\t<td>$_DateE</td>\n";
	echo "\t<td>". $_GET["len"]. "</td>\n";
	echo "\t<td>". $_GET["RID"]. "</td>\n";
	echo "\t<td>". $_GET["SchichtName"]. "</td>\n";
	
	
	// Ist eintarg schon vorhanden?	
	$SQL  = "SELECT `SID` FROM `Shifts` ";
	$SQL .=	"WHERE (".
		"`DateS` = '". $_DateS. "' AND ".
		"`DateE` = '". $_DateE. "' AND ".
		"`RID` = '". $_GET["RID"]. "');";
	$Erg = mysql_query($SQL, $con);
	
	if( mysql_num_rows($Erg) != 0 )
		echo "\t<td>exists</td>";
	elseif( $_GET["OnlyShow"] == "OFF" )   
	{
		// erstellt Eintrag in Shifts für die algemeine schicht
		$SQL  = "INSERT INTO `Shifts` ( `DateS`, `DateE`, `Len`, `RID`, `Man`) VALUES ( ";
		$SQL .= "'". $_DateS. "', '". $_DateE. "', ";
		$SQL .= "'". $_GET["len"]. "', '". $_GET["RID"]. "', ";
		$SQL .= "'". $_GET["SchichtName"]. "');";
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
		"`Len` = '". $_GET["len"]. "' AND ".
		"`RID` = '". $_GET["RID"]. "');";
	$Erg = mysql_query($SQL, $con);
	if( mysql_num_rows($Erg) == 0 )
		echo "\t<td>?</td>";
	else	
	{
		$SID = mysql_result($Erg, 0, "SID");
		echo "\t<td>". $SID. "</td>";
	}

	// erstellt für jeden Engeltypen die eintrage in 'ShiftEntry'
	echo "\t<td>";
	foreach ($EngelType As $TTemp)
	{
		$Temp = "EngelType".$TTemp["TID"];
		
		if( $_GET[$Temp] > 0 )
		{
			$i = 0;
			echo $_GET[$Temp]. " ".$TTemp["Name"]. "<br>\t";
			while( $i++ < $_GET[$Temp] )
			{
				if( $_GET["OnlyShow"] == "OFF" )
				{
					$SQL  = "INSERT INTO `ShiftEntry` (`SID`, `TID`) VALUES (";
					$SQL .= "'". $SID. "', ";
					$SQL .= "'". $TTemp["TID"]. "');";

					$Erg = mysql_query($SQL, $con);

					if( $DEBUG ) $SQLFail = "\n\t<br>[".$SQL. "]";

					if ($Erg == 1) echo "'pass' ";
					else           echo "'fail' <u>". mysql_error($con). "</u>$SQLFail</td>\n";

				}
				else
					echo "+";
			}
			echo "<br>";
		} // IF $$TEMP
	} // FOREACH
	echo "</td>";
	
	echo "</tr>\n";
}

include ("../../includes/footer.php");
?>
