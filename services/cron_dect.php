<?PHP

include ("../includes/config.php");
include ("../includes/config_IAX.php");
include ("../includes/config_db.php");
include ("../includes/error_handler.php");
include ("../includes/funktion_modem.php");
include ("../includes/funktion_cron.php");


//ausfuerungs Ruetmuss (in s)
$StartTimeBeforEvent = (60/4)*60; 
$AnrufDelay = -5;
$DebugDECT = FALSE;
#$DebugDECT = TRUE;

//Timeout erhöhen;
set_time_limit(50000); 

//SQL zusammensetzen
$SQL =  "SELECT Shifts.DateS, Shifts.RID, ShiftEntry.UID, ShiftEntry.TID ".
	"FROM `Shifts` INNER JOIN `ShiftEntry` ON `Shifts`.`SID` = `ShiftEntry`.`SID` ";
if( $DebugDECT)
	$SQL .= "WHERE (Shifts.DateS>'2007-07-09 09:45:00' AND ".
		"Shifts.DateS<='2007-07-09 11:00:00');";
else
	$SQL .= "WHERE ((`Shifts`.`DateS`>'". gmdate("Y-m-d H:i:s", time()+120+$gmdateOffset). "') AND ".
		"(`Shifts`.`DateS`<='". gmdate("Y-m-d H:i:s", time()+120+$gmdateOffset+$StartTimeBeforEvent). "') );";

$Erg = mysql_query($SQL, $con);
echo mysql_error($con);

$Z=0;
for( $i=0; $i<mysql_num_rows($Erg); $i++)
{  
   if( mysql_result($Erg, $i, "UID")>0)
   {
	$DECTnumber = UID2DECT(mysql_result($Erg, $i, "UID"));
	if( $DECTnumber!="")
	{
		echo "dial $DECTnumber\n";
		DialNumberIAX( $DECTnumber,
		               mysql_result($Erg, $i, "DateS"),
		               mysql_result($Erg, $i, "RID"),
			       mysql_result($Erg, $i, "TID"));
		DialNumberModem( $DECTnumber,
		                 mysql_result($Erg, $i, "DateS"));
		if( $Z++>10)
		{
			$Z=0;
			sleep(30);
		}
	}
  }
}

return 0;


?>

