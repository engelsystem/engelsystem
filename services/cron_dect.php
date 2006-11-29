<?PHP

include ("./inc/config.php");
include ("./inc/config_IAX.php");
include ("./inc/config_db.php");
include ("./inc/error_handler.php");
include ("./inc/funktion_modem.php");
include ("./inc/funktion_cron.php");

//ausfuerungs Ruetmuss (in s)
$StartTimeBeforEvent = (60/4)*60; 
$AnrufDelay = -5;
$DebugDECT=FALSE;

//Timeout erhöhen;
set_time_limit(50000); 

//SQL zusammensetzen
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
   if( mysql_result($Erg, $i, "UID")>0)
   {
	$DECTnumber = UID2DECT(mysql_result($Erg, $i, "UID"));
	if( $DECTnumber!="")
	{
		DialNumberIAX( $DECTnumber,
		               mysql_result($Erg, $i, "DateS"),
		               mysql_result($Erg, $i, "RID"),
			       mysql_result($Erg, $i, "TID"));
		DialNumberModem( $DECTnumber,
		                 mysql_result($Erg, $i, "DateS"));
	}
  }
}

return 0;


?>

