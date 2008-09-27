<?PHP
include "config_modem.php";

function DialNumber( $Number )
{
	global $Dev, $ModemEnable;

	if( $ModemEnable)
	{
		echo "Dial number: '<u>$Number</u>' was called<br>\n";
		$fp = fopen( $ModemDev, "w");
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
		echo "Modem is Disable, number: '<u>$Number</u>' was not called<br>\n";
}


function SetWackeup( $Number, $TimeH, $TimeM)
{
	global $WackupNumber;
	DialNumber( "$WackupNumber$TimeH$TimeM$Number");
}

?>
