<?PHP

$title = "DB Update from XML";
$header = "DB Update from XML";
$Page["Public"] = "N";
include ("./inc/header.php");
include ("./inc/funktion_user.php");
include ("./inc/funktion_xml.php");

///////////
// DEBUG //
///////////
$EnableRooms = true;
$EnableRoomsDB = true;
$EnableSchudle = true;
$EnableSchudleDB = true;
//$EnableRooms = false;
//$EnableRoomsDB = false;
//$EnableSchudle = false;
//$EnableSchudleDB = false;

/*##############################################################################################
				           F I L E
  ##############################################################################################*/
echo "\n\n<br>\n<h1>XML File:</h1>\n";
if( isset($_POST["PentabarfUser"]) && isset($_POST["PentabarfPasswd"]) && isset($_POST["PentabarfURL"]))
{
	echo "Update XML-File from Pentabarf..";
/*	$Command = "wget --http-user=". $_POST["PentabarfUser"]. " --http-passwd=".$_POST["PentabarfPasswd"]. " ".
			$_POST["PentabarfURL"].
			" --output-file=/tmp/engelXMLwgetLog --output-document=/tmp/engelXML";
*/	
	$Command = "lynx -auth=". $_POST["PentabarfUser"]. ":".$_POST["PentabarfPasswd"]. " -dump ".
			$_POST["PentabarfURL"].	" > /tmp/engelXML";
	echo system( $Command, $Status);

	if( $Status==0)
		echo "OK.<br>";
	else
		echo "fail ($Status)($Command).<br>";
}
else
{
	echo "<form action=\"dbUpdateFromXLS.php\" method=\"post\">\n";
	echo "<table border=\"0\">\n";
	echo "\t<tr><td>XML-File:</td>".
		"<td><input name=\"PentabarfURL\" type=\"text\" size=\"100\" maxlength=\"1000\" ".
		"value=\"https://pentabarf.cccv.de/pentabarf/xml/fahrplan/conference/1\"></td></tr>\n";
	echo "\t<tr><td>Username:</td>".
		"<td><input name=\"PentabarfUser\" type=\"text\" size=\"30\" maxlength=\"30\"></td></tr>\n";
	echo "\t<tr><td>Password:</td>".
		"<td><input name=\"PentabarfPasswd\" type=\"password\" size=\"30\" maxlength=\"30\"></td></tr>\n";
	echo "\t<tr><td></td><td><input type=\"submit\" name=\"FileUpload\" value=\"upload\"></td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
}



//readXMLfile("xml.php.xml");
readXMLfile("/tmp/engelXML");


/*
echo "<pre><br>";
echo $XMLmain->name;
echo "<br>";
//print_r(array_values ($XMLmain->sub));


echo "<br>";
$Feld=7;
echo "$Feld#". $XMLmain->sub[$Feld]->name. "<br>";
echo "$Feld#". $XMLmain->sub[$Feld]->sub;
//print_r(array_values ($XMLmain->sub[$Feld]->sub));
while(list($key, $value) = each($XMLmain->sub[$Feld]->sub))
	echo "?ID".$value->sub[1]->data. "=". $value->sub[2]->data. "\n";

echo "</pre>";
*/

/*##############################################################################################
				           V e r s i o n
  ##############################################################################################*/
echo "<hr>\n";
$XMLrelease = getXMLsubPease( $XMLmain, "RELEASE");
echo "release: ". $XMLrelease->data. "<br>\n";
$XMLreleaseDate = getXMLsubPease( $XMLmain, "RELEASE-DATE");
echo "release date: ". $XMLreleaseDate->data. "<br>\n";
echo "<hr>\n";



/*##############################################################################################
				           R o o m
  ##############################################################################################*/
echo "\n\n<br>\n<h1>Rooms:</h1>\n";

function saveRoomData()
{
	include ("./inc/db.php");
	if( isset($_GET["RID"]) && isset($_GET["NumberXML"]) && isset($_GET["NameXML"]))
	{
		$SQL1 = "SELECT `RID` FROM `Room` ".
			"WHERE `RID` = '". $_GET["RID"]. "';";
		$Erg1 = mysql_query($SQL1, $con);
		
		if( mysql_num_rows($Erg1)==1 )
			$SQL= "UPDATE `Room` SET `Name` = '". mysql_escape_string($_GET["NameXML"]). 
				"', `FromPentabarf`='Y', `Number`='". $_GET["NumberXML"]. "' ".
				"WHERE `RID` = '". $_GET["RID"]. "' LIMIT 1;";
		else
			$SQL= "INSERT INTO `Room` ( `RID` , `Name`, `FromPentabarf`, `Number` ) ".
				"VALUES ('". $_GET["RID"]. "', '". mysql_escape_string($_GET["NameXML"]). 
					  "', 'Y', ". $_GET["NumberXML"]. ");";
		$Erg = mysql_query($SQL, $con);
		if( $Erg )
			echo "Aenderung, an Raum ". $_GET["NameXML"]. ", war erfogreich<br>";
		else
			echo "Aenderung, an Raum ". $_GET["NameXML"]. ", war <u>nicht</u> erfogreich.(".
				mysql_error($con). ")<br>[$SQL]<br>";
	}
	else 
		echo "Fehler in den Parametern!<br>";
} /*function saveRoomData*/

function getDBRoomName( $RID)
{
	include ("./inc/db.php");
	$SQL = "SELECT Name FROM `Room` WHERE RID=$RID";
	$Erg = mysql_query($SQL, $con);
	if(mysql_num_rows($Erg)>0)
		return mysql_result($Erg, 0, 0);
	else
		return "";
} /*function getDBRoomName*/

function getDBRoomNumber( $RID)
{
	include ("./inc/db.php");
	$SQL = "SELECT Number FROM `Room` WHERE RID=$RID";
	$Erg = mysql_query($SQL, $con);
	if(mysql_num_rows($Erg)>0)
		return mysql_result($Erg, 0, 0);
	else
		return "";
} /*function getDBRoomNumber*/



if( isset($_GET["RoomUpdate"]))
	saveRoomData();

//INIT Status counter
$DS_OK = 0;
$DS_KO = 0;
$Where = "";

//Ausgabe
echo "<table border=\"0\">\n";
echo "<tr><th>RID</th><th>NumberXML</th><th>NumberDB</th><th>NameXML</th><th>NameDB</th><th>state</th></tr>\n";
$XMLroom = getXMLsubPease( $XMLmain, "ROOMS");
while( (list($key, $value) = each($XMLroom->sub)) && $EnableRooms)
{
	$XMLRID  = getXMLsubPease( $value, "ID");
	$RID     = $XMLRID->data;
	$XMLNumber = getXMLsubPease( $value, "NUMBER");
	$NumberXML = trim($XMLNumber->data);
	$XMLName = getXMLsubPease( $value, "NAME");
	$NameXML = trim($XMLName->data);
	
	if( isset($_GET["UpdateALL"]))
	{
		$_GET["NameXML"] = $NameXML;
		$_GET["NumberXML"] = $NumberXML;
		$_GET["RID"] = $RID;
		saveRoomData();
	}
	
	$NameDB    = convertValues(getDBRoomName($RID));
	$NumberDB  = convertValues(getDBRoomNumber($RID));
	
	echo "<form action=\"dbUpdateFromXLS.php\">\n";
	echo "\t<tr>\n";
	echo "\t<td><input name=\"RID\" type=\"text\" value=\"$RID\" size=\"1\" eadonly></td>\n";
	echo "\t<td><input name=\"NumberXML\" type=\"text\" value=\"$NumberXML\" size=\"1\" readonly></td>\n";
	echo "\t<td><input name=\"NumberDB\" type=\"text\" value=\"$NumberDB\" size=\"1\"readonly></td>\n";
	echo "\t<td><input name=\"NameXML\" type=\"text\" value=\"$NameXML\" readonly></td>\n";
	echo "\t<td><input name=\"NameDB\" type=\"text\" value=\"$NameDB\" readonly></td>\n";
	if( !(	$NameXML==$NameDB && $NumberXML==$NumberDB) )
	{
		echo "\t<td><input type=\"submit\" name=\"RoomUpdate\" value=\"update\"></td>\n";
		$DS_KO++;
	}
	else
	{
		echo "\t<td>OK</td>\n";
		$DS_OK++;
	}
	echo "\t</tr>\n";
	echo "</form>\n";
	$Where.= " OR RID=$RID";
}
echo "<tr><td colspan=\"6\">status: $DS_KO/$DS_OK nicht Aktuel.</td></tr>\n";

//Anzeige von nicht im XML File vorkommende entraege
$SQL2 = "SELECT * FROM `Room` WHERE NOT (".substr( $Where, 4). ") AND FromPentabarf =  'Y';";
$Erg2 = mysql_query($SQL2, $con);
if( mysql_num_rows($Erg2)>0 && $EnableRoomsDB )
	for( $i=0; $i<mysql_num_rows( $Erg2); $i++)
	{
		$RID      = mysql_result( $Erg2, $i, "RID");
		$NumberDB = mysql_result( $Erg2, $i, "Number");
		$NameDB   = mysql_result( $Erg2, $i, "Name");
		echo "\t<tr>\n";
		echo "\t<td><input name=\"RID\" type=\"text\" value=\"$RID\" size=\"1\" eadonly></td>\n";
		echo "\t<td><input name=\"NumberXML\" type=\"text\" value=\"\" size=\"1\" readonly></td>\n";
		echo "\t<td><input name=\"NumberDB\" type=\"text\" value=\"$NumberDB\" size=\"1\"readonly></td>\n";
		echo "\t<td><input name=\"NameXML\" type=\"text\" value=\"\" readonly></td>\n";
		echo "\t<td><input name=\"NameDB\" type=\"text\" value=\"$NameDB\" readonly></td>\n";
		echo "\t<td><a href=\"./room.php?action=change&RID=$RID\">edit</a></td>\n";
		echo "\t<tr>\n";
	}

echo "</table>\n";




/*##############################################################################################
				        S c h e d u l e 
  ##############################################################################################*/
echo "\n\n<h1>Schudle:</h1>\n";

// erstellt ein Array der Reume
        $sql = "SELECT * FROM `Room` ".
               "ORDER BY `Number`, `Name`;";
	$Erg = mysql_query($sql, $con);
	for( $i=0; $i<mysql_num_rows($Erg); $i++)
		for( $j=0; $j<mysql_num_fields( $Erg); $j++)
			$RoomID[ mysql_result($Erg, $i, "RID")]
			       [ mysql_field_name($Erg, $j)] = mysql_result($Erg, $i, $j);
																

function SaveSchedule()
{
	global  $RoomID;

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
				foreach ($RoomID[ $_GET["RIDXML"]] as $Key => $Value)
					if( substr( $Key, 0, 12)=="DEFAULT_EID_" && $Value > 0 )
					{
						echo "---->Create engeltype: ". substr( $Key, 12). " ". $Value. "x<br>\n";
						$i=0;
						while( $i++ < $Value )
						{
							$SQL3  = "INSERT INTO `ShiftEntry` (`SID`, `TID`) VALUES (".
								 "'". $_GET["SIDXML"]. "', '". substr( $Key, 12). "');";

							$Erg = mysql_query($SQL3, $con);

							if ($Erg == 1) 
								echo "------>pass<br>\n";
							else
								echo "------>fail <u>". mysql_error($con). 
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
$XMLevents = getXMLsubPease( $XMLmain, "EVENTS");
while( (list($EventKey, $Event) = each($XMLevents->sub)) && $EnableSchudle)
{	
	echo "<form action=\"dbUpdateFromXLS.php\">\n";
	echo "\t<tr>\n";
	
	$DateXML = substr($Event->attributes["START"], 0, 10). " ".
		   substr($Event->attributes["START"], 11). ":00";
	$LenXML  = $Event->attributes["DURATION"];
		$LenXML  = substr( $LenXML, 0, 2) + (substr($LenXML, 3, 2)/60);
	$XMLeventID = getXMLsubPease( $Event, "ID");
		$SIDXML  = $XMLeventID->data;
	$RIDXML  = $Event->attributes["ROOM-ID"];
	$XMLTitle = getXMLsubPease( $Event, "TITLE");
		$ManXML  = $XMLTitle->data;
			
	if( isset($_GET["UpdateALL"]))
	{
		$_GET["SIDXML"]  = $SIDXML;
		$_GET["DateXML"] = "$DateXML $TimeXML";
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
		$RIDDB  = mysql_result($Erg, 0, "RID");
		$ManDB  = mysql_result($Erg, 0, "Man");
	}
	else
		$SIDDB  = $TimeDB = $LenDB  = $RIDDB  = $ManDB= "";
	echo "\t<td><input name=\"SIDXML\" type=\"text\" value=\"$SIDXML\" size=\"2\" eadonly></td>\n";
	echo "\t<td><input name=\"DateXML\" type=\"text\" value=\"$DateXML\" size=\"17\" readonly>\n\t\t".
		   "<input name=\"DateDB\" type=\"text\" value=\"$TimeDB\" size=\"17\" readonly></td>\n";
	echo "\t<td><input name=\"RIDXML\" type=\"text\" value=\"$RIDXML\" size=\"1\" readonly>\n\t\t".
		   "<input name=\"RIDDB\" type=\"text\" value=\"$RIDDB\" size=\"1\" readonly></td>\n";
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
		echo "\t<td>OK</td>\n";
		$DS_OK++;
	}
	echo "\t</tr>\n";
	echo "</form>\n";
	$Where.= " OR SID=$SIDXML";		
}
echo "<tr><td colspan=\"6\">status: $DS_KO/$DS_OK nicht Aktuel.</td></tr>\n";

//Anzeige von nicht im XML File vorkommende entraege
$SQL2 = "SELECT * FROM `Shifts` WHERE NOT (".substr( $Where, 4). ") AND FromPentabarf =  'Y';";
$Erg2 = mysql_query($SQL2, $con);
if(mysql_num_rows($Erg2)>0 && $EnableSchudleDB )
	for( $i=0; $i<mysql_num_rows( $Erg2); $i++)
	{
		echo "\t<tr>\n";
		$SID  = mysql_result($Erg2, $i, "SID");
		$Time = mysql_result($Erg2, $i, "DateS");
		$Len  = mysql_result($Erg2, $i, "Len");
		$RID  = mysql_result($Erg2, $i, "RID");
		$Man  = mysql_result($Erg2, $i, "Man");
		echo "\t<td><input name=\"SIDXML\" type=\"text\" value=\"$SID\" size=\"2\" eadonly></td>\n";
		echo "\t<td><input name=\"DateXML\" type=\"text\" value=\"\" size=\"17\" readonly>\n\t\t".
			   "<input name=\"DateDB\" type=\"text\" value=\"$Time\" size=\"17\" readonly></td>\n";
		echo "\t<td><input name=\"RIDXML\" type=\"text\" value=\"\" size=\"1\" readonly>\n\t\t".
			   "<input name=\"RIDDB\" type=\"text\" value=\"$RID\" size=\"1\" readonly></td>\n";
		echo "\t<td><input name=\"LenXML\" type=\"text\" value=\"\" size=\"1\"readonly>\n\t\t".
			   "<input name=\"LenDB\" type=\"text\" value=\"$Len\" size=\"1\"readonly></td>\n";
		echo "\t<td><input name=\"ManXML\" type=\"text\" value=\"\" size=\"40\"readonly>\n\t\t".
			   "<input name=\"ManDB\" type=\"text\" value=\"$Man\" size=\"40\"readonly></td>\n";
		echo "\t<td><a href=\"./schichtplan.php?action=change&SID=$SID\">edit</a></td>\n";
		echo "\t<tr>\n";
	}
echo "</table>";



/*##############################################################################################
				         U P D A T E  A L L 
  ##############################################################################################*/
echo "\n\n<br>\n<h1>Update ALL:</h1>\n";

echo "<form action=\"dbUpdateFromXLS.php\">\n";
echo "\t<input type=\"submit\" name=\"UpdateALL\" value=\"now\">\n";
echo "</form>\n";


include ("./inc/footer.php");
?>

