<?PHP

function UID2DECT($UID) 
{
	global $con;
	$SQL = "SELECT DECT FROM `User` WHERE UID='$UID'";
	$Erg = mysql_query($SQL, $con);
	if( mysql_num_rows( $Erg) == 1)
		return mysql_result($Erg, 0);
	else
		return "";
}
function RID2Room($RID) 
{
	global $con;
	$SQL = "SELECT Name FROM `Room` WHERE RID='$RID'";
	$Erg = mysql_query($SQL, $con);
	if( mysql_num_rows( $Erg) == 1)
		return mysql_result($Erg, 0);
	else
		return "";
}

function TID2Engeltype($TID) 
{
	global $con;
	$SQL = "SELECT Name FROM `EngelType` WHERE TID='$TID'";
	$Erg = mysql_query($SQL, $con);
	if( mysql_num_rows( $Erg) == 1)
		return mysql_result($Erg, 0);
	else
		return "";
}


function DialNumberIAX( $DECTnumber, $Time, $RID, $TID)
{
	global $IAXenable, $IAXcontent, $IAXserver, $AnrufDelay, $DebugDECT, $Tempdir, $AsteriskOutputDir;
	
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
	
	if( $IAXenable)
	{	$Message="die-nee shisht beh-kinned , in where-neegin me-nooten . . . your shift beginns in a few minutes";
		if (isset($SetHttpIAX))
		{
			$post_data = array();
			$post_data['code'] = "89o8eu9cg4";
			$post_data['callerid'] = "1023";
			$post_data['nr'] = "$DECTnumber";
			//$post_data['message'] = "Deine schicht beginnt in ein paar minuten . . . your shift beginns in a few minutes ";
			$post_data['message'] = "die-nee shisht beh-kinned , in where-neegin me-nooten . . . your shift beginns in a few minutes ";
			$url = "https://23c3.eventphone.de/~bef/call.php";

			$o="";
			foreach ($post_data as $k=>$v)
			{
			   $o.= "$k=".urlencode(utf8_encode($v))."&";
			}
			$post_data=substr($o,0,-1);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_URL, $url);   
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$result = curl_exec($ch);
			echo curl_error($ch);
			curl_close($ch);
		}
		else
		{
			// IAX file Schareiebn
			$CallFile = $Tempdir. "/call_". date("Ymd_His"). "_$DECTnumber";
	
			if($DebugDECT) echo "IAX create file for dialing Number $DECTnumber\n";
			$file = fopen( $CallFile, 'w' );
			if( $file != FALSE)
			{
				fputs( $file, "Channel: SIP/$DECTnumber@$IAXserver\n");  //Ziel nummer
				fputs( $file, "Callerid: Engelserver\n");
	//			fputs( $file, "Callerid: $IAXcontent\n");
 	//				fputs( $file, "Context: $DECTnumber@$IAXserver\n");
				fputs( $file, "Extension: s\n");
				fputs( $file, "MaxRetries: 1\n");
				fputs( $file, "RetryTime: 10\n");
				fputs( $file, "SetVar: msg=$Message\n");
//				fputs( $file, "SetVar: TimeH=$TimeH\n");
//				fputs( $file, "SetVar: TimeM=$TimeM\n");
//				fputs( $file, "SetVar: DECTnumber=$DECTnumber\n");
//				fputs( $file, "SetVar: Room=".  RID2Room( $RID). "\n");
//				fputs( $file, "SetVar: Engeltype=". TID2Engeltype( $TID). "\n");
				fclose($file);
				system( "chmod 777 ". $CallFile);
				system( "mv ". $CallFile. " ". $AsteriskOutputDir);
				
			}
			else
				echo "error: $CallFile not created";
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

