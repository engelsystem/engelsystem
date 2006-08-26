<?PHP

include ("./inc/db.php");
include ("./inc/config.php");

//ausfuerungs Ruetmuss (in s)
$StartTimeBeforEvent = (60/4)*60; 
$DebugDECT=FALSE;

//Setting Asterisk
$Content="Engelsystem";
$IAXserver="Engelsystem:engelengel@10.1.1.1";

function DialNumber( $DECTnumber, $TimeH, $TimeM, $Room, $Engeltype)
{
	global $Content, $IAXserver, $ModemEnable;
	
	$CallFile = "/tmp/call_". date("Ymd_His"). "_$DECTnumber";
	if( $ModemEnable)
	{
		$file = fopen( $CallFile, 'w' );
		if( $file != FALSE)
		{
			fputs( $file, "Channel: IAX2/$IAXserver/Engelserver@$Content\n");
			fputs( $file, "Callerid: $Content\n");
			fputs( $file, "Context: $Content\n");
			fputs( $file, "Extension: s\n");
			fputs( $file, "MaxRetries: 3\n");
			fputs( $file, "RetryTime: 10\n");
			fputs( $file, "SetVar: TimeH=$TimeH\n");
			fputs( $file, "SetVar: TimeM=$TimeM\n");
			fputs( $file, "SetVar: DECTnumber=$DECTnumber\n");
			fputs( $file, "SetVar: Room=$Room\n");
			fputs( $file, "SetVar: Engeltype=$Engeltype\n");
			fclose($file);

			system( "cat $CallFile");
			system( "chmod 777 $CallFile");
			system( "mv $CallFile /var/spool/asterisk/outgoing");
		}
	} 
	else 
		 echo "Modem is Disable, number:'$DECTnumber' with the Parameter, Time:'$TimeH:$TimeM', Room:'$Room', Type:'$Engeltype' was called<br>\n";
}

function UID2DECT($UID) 
{
	global $con;
	$SQL = "SELECT DECT FROM `User` WHERE UID='$UID'";
	$Erg = mysql_query($SQL, $con);
	return mysql_result($Erg, 0);
}
function RID2Room($RID) 
{
	global $con;
	$SQL = "SELECT Name FROM `Room` WHERE RID='$RID'";
	$Erg = mysql_query($SQL, $con);
	return mysql_result($Erg, 0);
}

function TID2Engeltype($TID) 
{
	global $con;
	$SQL = "SELECT Name FROM `EngelType` WHERE TID='$TID'";
	$Erg = mysql_query($SQL, $con);
	return mysql_result($Erg, 0);
}



$SQL =  "SELECT Shifts.DateS, Shifts.RID, ShiftEntry.UID, ShiftEntry.TID ".
	"FROM `Shifts` INNER JOIN `ShiftEntry` ON `Shifts`.`SID` = `ShiftEntry`.`SID` ";
if( $DebugDECT)
	$SQL .= "WHERE (Shifts.DateS>'2004-12-27 10:45:00' AND ".
		"Shifts.DateS<='2004-12-27 11:00:00');";
else
	$SQL .= "WHERE ((`Shifts`.`DateS`>'". gmdate("Y-m-d H:i:s", time()+3600+120). "') AND ".
		"(`Shifts`.`DateS`<='". gmdate("Y-m-d H:i:s", time()+3600+120+$StartTimeBeforEvent). "') );";

$Erg = mysql_query($SQL, $con);

echo mysql_error($con);

for( $i=0; $i<mysql_num_rows($Erg); $i++)
{  
//   echo mysql_result($Erg, $i, "UID");
   if( mysql_result($Erg, $i, "UID")>0)
   {
	$SQL2 = "SELECT DECT FROM `User` WHERE ( `UID`='". mysql_result($Erg, $i, "UID"). "');";
	$Erg2 = mysql_query($SQL2, $con);

	$Number = mysql_result($Erg2, 0, "DECT");
	if( $Number!="")
	{
		$TimeH = substr( mysql_result($Erg, $i, "DateS"), 11, 2);
		$TimeM = substr( mysql_result($Erg, $i, "DateS"), 14, 2);
		$TimeM = substr( mysql_result($Erg, $i, "DateS"), 14, 2) - 5;
		if( $TimeM < 0 )
		{
			$TimeM += 60;
			$TimeH -= 1;
		}
		if( $TimeH < 0 )
			$TimeH += 24;
		
		if( strlen( $TimeH) == 1)
			$TimeH = "0".$TimeH;
		
		$Room = RID2Room( mysql_result($Erg, $i, "RID"));
		$EngelType = TID2Engeltype( mysql_result($Erg, $i, "TID"));
		DialNumber( $Number, $TimeH, $TimeM, $Room, $EngelType);
	}
  }
}

return 0;


?>

