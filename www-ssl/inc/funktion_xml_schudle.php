<?PHP

/*##############################################################################################
				        S c h e d u l e 
  ##############################################################################################*/
echo "\n\n<h1>Schudle:</h1>\n";


function SaveSchedule()
{
	global  $RoomID, $RoomName;

	include ("./inc/db.php");
	if( isset($_GET["SIDXML"]) && 
	    isset($_GET["DateXML"]) &&
	    isset($_GET["RIDXML"]) &&
	    isset($_GET["LenXML"]) &&
	    isset($_GET["ManXML"])  )
	{
		//erzeuge von `DateE`
		$TimeStart = substr( $_GET["DateXML"], 11, 2) + (substr($_GET["DateXML"], 14, 2)/60);
		$TimeEnd  = ($_GET["LenXML"] + $TimeStart) * 60;
		$TimeM = $TimeEnd % 60;
		$TimeH = ($TimeEnd - $TimeM)/60;
		if( $TimeH>=24 )
		{
			$TimeH -= 24;
			$DateEnd = substr($_GET["DateXML"], 0, 8). 
				   (substr($_GET["DateXML"], 8, 2)+1). " ";
		}
		else
			$DateEnd = substr($_GET["DateXML"], 0, 11);
		$DateEnd .= "$TimeH:$TimeM:00";
		
		//Namen ermitteln
		$_GET["RIDXML"] = $RoomName[$_GET["RIDXML"]];
		
		//Update OR insert ?
		$SQL1 = "Select `SID` FROM `Shifts` WHERE `SID`='". $_GET["SIDXML"]. "';";
		$Erg1 =  mysql_query($SQL1, $con);
		
		if( mysql_num_rows($Erg1)==0)
			$SQL= "INSERT INTO `Shifts` (`SID`, `DateS`, `DateE`, `Len`, `RID`, `Man`, `FromPentabarf`) ".
				"VALUES ('". $_GET["SIDXML"]. "', '". $_GET["DateXML"]. "', '". 
					     $DateEnd. "', '". $_GET["LenXML"]. "', '". 
					     $_GET["RIDXML"]. "', '". mysql_escape_string($_GET["ManXML"]). "', 'Y');";
		else
			$SQL= "UPDATE `Shifts` SET ".
				"`DateS` = '". $_GET["DateXML"]. "', ".
				"`DateE` = '". $DateEnd. "', ".
				"`Len` = '". $_GET["LenXML"]. "', ".
				"`RID` = '". $_GET["RIDXML"]. "', ".
				"`Man` = '". mysql_escape_string($_GET["ManXML"]). "', ".
				"`FromPentabarf`= 'Y' ".
				"WHERE `SID` = '". $_GET["SIDXML"]. "' LIMIT 1;";
		$Erg = mysql_query($SQL, $con);
		if( $Erg )
		{
			echo "Aenderung, am Schedule '". $_GET["SIDXML"]. "', war erfogreich<br>\n";
			if( mysql_num_rows($Erg1)==0)
			{
				echo "-->Create Shifts:<br>\n";
				
				// erstellt ein Array der Reume
			        $sql2 =	"SELECT * FROM `Room` ".
					"WHERE `RID` = ".$_GET["RIDXML"]. " ".
		        		"ORDER BY `Number`, `Name`;";
				$Erg2 = mysql_query( $sql2, $con);
				for( $j=0; $j<mysql_num_fields( $Erg2); $j++)
					if( substr( mysql_field_name($Erg2, $j), 0, 12)=="DEFAULT_EID_" && 
					    mysql_result($Erg2, 0, $j) > 0 )
					{
						echo "---->Create engeltype: ". substr( mysql_field_name($Erg2, $j), 12). 
							" ".  mysql_result($Erg2, 0, $j). "x<br>\n";
						for( $i=0; $i < mysql_result($Erg2, 0, $j); $i++ )
						{
							$SQL3  = "INSERT INTO `ShiftEntry` (`SID`, `TID`) VALUES (".
								 "'". $_GET["SIDXML"]. "', ".
								 "'". substr(  mysql_field_name($Erg2, $j), 12). "');";

							$Erg3 = mysql_query($SQL3, $con);
							if ($Erg3 == 1) 
								echo "------>pass<br>\n";
							else
								echo "------>fail <u>". 
									mysql_error($con). 
									"</u>($SQL3)<br>\n";
						}
						
					}
				echo "<br>\n";
			}
		}
		else
			echo "Aenderung, am Schedule '". $_GET["SIDXML"]. "', war <u>nicht</u> erfogreich.(". 
				mysql_error($con). ")<br>[$SQL]<br>\n";
	}
	else 
		echo "Fehler in den Parametern!<br>";
} /*SaveSchedule*/

if( isset($_GET["ScheduleUpdate"]))
	SaveSchedule();

//INIT Status counter
$DS_OK = 0;
$DS_KO = 0;
$Where = "";

//ausgabe
echo "<table border=\"0\">\n";
echo "<tr><th>SID</th><th>Date</th>".
	"<th>Room</th><th>Len</th><th>Name</th><th>state</th></tr>\n";
echo "<tr align=\"center\"><td>XML - DB</td><td>XML - DB</td>".
	"<td>XML - DB</td><td>XML - DB</td><td>XML - DB</td><td></td></tr>\n";

if( $EnableSchudle)
foreach($XMLmain->sub as $EventKey => $Event)
{
	if( $Event->name == "VEVENT")
	{
		echo "<form action=\"dbUpdateFromXLS.php\">\n";
		echo "\t<tr>\n";
	
		$SIDXML  = substr( getXMLsubData( $Event, "UID"), 0, strpos( getXMLsubData( $Event, "UID"), "@" )); 
		$DateXML = 
			substr( getXMLsubData( $Event, "DTSTART"), 0, 4). "-".
			substr( getXMLsubData( $Event, "DTSTART"), 4, 2). "-".
			substr( getXMLsubData( $Event, "DTSTART"), 6, 2). " ".
			   substr( getXMLsubData( $Event, "DTSTART"), 9, 2). ":".
			   substr( getXMLsubData( $Event, "DTSTART"), 11,2). ":00";
		$LenXML  = substr( getXMLsubData( $Event, "DURATION"), 0, strlen(getXMLsubData( $Event, "DURATION"))-1);
		$RIDXML  = getXMLsubData( $Event, "LOCATION");
		$ManXML  = getXMLsubData( $Event, "SUMMARY");
	
		if( isset($_GET["UpdateALL"]))
		{
			$_GET["SIDXML"]  = $SIDXML;
			$_GET["DateXML"] = $DateXML;
			$_GET["LenXML"]  = $LenXML;
			$_GET["RIDXML"]  = $RIDXML;
			$_GET["ManXML"]  = $ManXML;
			SaveSchedule();
		}
			
		$SQL = "SELECT * FROM `Shifts` WHERE SID=$SIDXML";
		$Erg = mysql_query($SQL, $con);
		if(mysql_num_rows($Erg)>0)
		{
			$SIDDB  = mysql_result($Erg, 0, "SID");
			$TimeDB = mysql_result($Erg, 0, "DateS");
			$LenDB  = mysql_result($Erg, 0, "Len");
			if( isset($RoomID[mysql_result($Erg, 0, "RID")]))
				$RIDDB  = $RoomID[mysql_result($Erg, 0, "RID")];
			else
				$RIDDB  = "RID". mysql_result($Erg, 0, "RID");
			
			$ManDB  = mysql_result($Erg, 0, "Man");
		}
		else
			$SIDDB  = $TimeDB = $LenDB  = $RIDDB  = $ManDB= "";

		echo "\t<td><input name=\"SIDXML\" type=\"text\" value=\"$SIDXML\" size=\"2\" eadonly></td>\n";
		echo "\t<td><input name=\"DateXML\" type=\"text\" value=\"$DateXML\" size=\"17\" readonly>\n\t\t".
		   "<input name=\"DateDB\" type=\"text\" value=\"$TimeDB\" size=\"17\" readonly></td>\n";
		echo "\t<td><input name=\"RIDXML\" type=\"text\" value=\"$RIDXML\" size=\"15\" readonly>\n\t\t".
		   "<input name=\"RIDDB\" type=\"text\" value=\"$RIDDB\" size=\"15\" readonly></td>\n";
		echo "\t<td><input name=\"LenXML\" type=\"text\" value=\"$LenXML\" size=\"1\"readonly>\n\t\t".
		   "<input name=\"LenDB\" type=\"text\" value=\"$LenDB\" size=\"1\"readonly></td>\n";
		echo "\t<td><input name=\"ManXML\" type=\"text\" value=\"$ManXML\" size=\"40\"readonly>\n\t\t".
		   "<input name=\"ManDB\" type=\"text\" value=\"$ManDB\" size=\"40\"readonly></td>\n";
		if( !(	$SIDXML==$SIDDB && 
			$DateXML==$TimeDB && 
			$RIDXML==$RIDDB && 
			$LenXML==$LenDB &&
			$ManXML==$ManDB) )
		{
			echo "\t<td><input type=\"submit\" name=\"ScheduleUpdate\" value=\"update\"></td>\n";
			$DS_KO++;
		}
		else
		{
			echo "\t<td><a href=\"./schichtplan.php?action=change&SID=$SIDXML\">edit</a></td>\n";
			$DS_OK++;
		}
		echo "\t</tr>\n";
		echo "</form>\n";
		$Where.= " OR SID=$SIDXML";		

		}
}
echo "<tr><td colspan=\"6\">status: $DS_KO/$DS_OK nicht Aktuel.</td></tr>\n";


//Anzeige von nicht im XML File vorkommende entraege
if( $Where =="")
	$SQL2 = "SELECT * FROM `Shifts` WHERE FromPentabarf =  'Y';";
else
	$SQL2 = "SELECT * FROM `Shifts` WHERE NOT (".substr( $Where, 4). ") AND FromPentabarf =  'Y';";
	
$Erg2 = mysql_query($SQL2, $con);
if(mysql_num_rows($Erg2)>0 && $EnableSchudleDB )
	for( $i=0; $i<mysql_num_rows( $Erg2); $i++)
	{
		echo "\t<tr>\n";
		$SID  = mysql_result($Erg2, $i, "SID");
		$Time = mysql_result($Erg2, $i, "DateS");
		$Len  = mysql_result($Erg2, $i, "Len");
		if( isset($RoomID[ mysql_result($Erg2, $i, "RID")]))
			$RID  = $RoomID[ mysql_result($Erg2, $i, "RID")];
		else
			$RID  = "RID.". mysql_result($Erg2, $i, "RID");
		$Man  = mysql_result($Erg2, $i, "Man");
		echo "\t<td><input name=\"SIDXML\" type=\"text\" value=\"$SID\" size=\"2\" eadonly></td>\n";
		echo "\t<td><input name=\"DateXML\" type=\"text\" value=\"\" size=\"17\" readonly>\n\t\t".
			   "<input name=\"DateDB\" type=\"text\" value=\"$Time\" size=\"17\" readonly></td>\n";
		echo "\t<td><input name=\"RIDXML\" type=\"text\" value=\"\" size=\"15\" readonly>\n\t\t".
			   "<input name=\"RIDDB\" type=\"text\" value=\"$RID\" size=\"15\" readonly></td>\n";
		echo "\t<td><input name=\"LenXML\" type=\"text\" value=\"\" size=\"1\"readonly>\n\t\t".
			   "<input name=\"LenDB\" type=\"text\" value=\"$Len\" size=\"1\"readonly></td>\n";
		echo "\t<td><input name=\"ManXML\" type=\"text\" value=\"\" size=\"40\"readonly>\n\t\t".
			   "<input name=\"ManDB\" type=\"text\" value=\"$Man\" size=\"40\"readonly></td>\n";
		echo "\t<td><a href=\"./schichtplan.php?action=change&SID=$SID\">edit</a></td>\n";
		echo "\t<tr>\n";
	}
echo "</table>";


?>
