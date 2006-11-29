<?PHP

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


function DialNumberIAX( $DECTnumber, $Time, $RID, $TID)
{
	global $IAXenable, $IAXcontent, $IAXserver, $AnrufDelay, $DebugDECT;
	
	//Parameter verarbeiten
	$TimeH = substr( $Time, 11, 2);
	$TimeM = substr( $Time, 14, 2);
	$TimeM = substr( $Time, 14, 2) + $AnrufDelay;
	if( $TimeM < 0 )
	{
		$TimeM += 60;
		$TimeH -= 1;
	}
	if( $TimeH < 0 )
		$TimeH += 24;
	
	if( strlen( $TimeH) == 1)
		$TimeH = "0".$TimeH;
	
	// IAX file Schareiebn
	$CallFile = "/tmp/call_". date("Ymd_His"). "_$DECTnumber";
	
	if( $IAXenable)
	{
		if($DebugDECT) echo "IAX create file for dialing Number $DECTnumber\n";
		$file = fopen( $CallFile, 'w' );
		if( $file != FALSE)
		{
			fputs( $file, "Channel: IAX2/$IAXserver/Engelserver@$IAXcontent\n");
			fputs( $file, "Callerid: $IAXcontent\n");
			fputs( $file, "Context: $IAXcontent\n");
			fputs( $file, "Extension: s\n");
			fputs( $file, "MaxRetries: 3\n");
			fputs( $file, "RetryTime: 10\n");
			fputs( $file, "SetVar: TimeH=$TimeH\n");
			fputs( $file, "SetVar: TimeM=$TimeM\n");
			fputs( $file, "SetVar: DECTnumber=$DECTnumber\n");
			fputs( $file, "SetVar: Room=".  RID2Room( $RID). "\n");
			fputs( $file, "SetVar: Engeltype=". TID2Engeltype( $TID). "\n");
			fclose($file);
			system( "chmod 777 $CallFile");
			system( "mv $CallFile /var/spool/asterisk/outgoing");
		}
	}
	else
		if($DebugDECT) echo "IAX is disable\n";
}

function DialNumberModem( $DECTnumber, $Time)
{
	global $AnrufDelay;
	
	//Parameter verarbeiten
	$TimeH = substr( $Time, 11, 2);
	$TimeM = substr( $Time, 14, 2);
	$TimeM = substr( $Time, 14, 2) + $AnrufDelay;
	if( $TimeM < 0 )
	{
		$TimeM += 60;
		$TimeH -= 1;
	}
	if( $TimeH < 0 )
		$TimeH += 24;
	
	if( strlen( $TimeH) == 1)
		$TimeH = "0".$TimeH;
	
	SetWackeup( $DECTnumber, $TimeH, $TimeM);
}

return 0;

?>

