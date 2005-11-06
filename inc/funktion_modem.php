<?PHP
	
$Dev="/dev/ttyS0";	// COM port
$WackupNumber="**3";

//ob_end_flush();		//ausgabe obwohl skript nich in arbeit
set_time_limit(50000);	//Timeout erhöhen;

function DialNumber( $Number )
{
	global $Dev, $ModemEnable;

echo $Number;

	if( $ModemEnable)
	{
		$fp = fopen( $Dev, "w");
		sleep(1);
		fwrite( $fp, "+++");
		sleep(1);
		fwrite( $fp, "ATZ\n");
		sleep(1);
		fwrite( $fp, "ATX1\n");
		sleep(1);
		fwrite( $fp, "ATD $Number \n");
		sleep(8);
		fclose($fp);
		sleep(1);
	}
	else
	{
		echo "Modem is Disable, number: '<u>$Number</u>' was called<br>\n";
	}
}


function SetWackeup( $Number, $TimeH, $TimeM)
{
	global $WackupNumber;
	DialNumber( "$WackupNumber$TimeH$TimeM$Number");
}

?>
