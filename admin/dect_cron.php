<?PHP


include ("./inc/db.php");
include ("./inc/config.php");
include ("./inc/funktion_modem.php");

//ausfuerungs Ruetmuss (in s)
$StartTimeBeforEvent = (60/4)*60; 




function UID2DECT($UID) 
{
	include ("./inc/db.php");

	$SQL = "SELECT DECT FROM `User` WHERE UID='$UID'";
	$Erg = mysql_query($SQL, $con);

	return mysql_result($Erg, 0);
}


$SQL =  "SELECT Shifts.DateS, ShiftEntry.UID ".
	"FROM `Shifts` INNER JOIN `ShiftEntry` ON `Shifts`.`SID` = `ShiftEntry`.`SID` ".
	"WHERE ((`Shifts`.`DateS`>'". gmdate("Y-m-d H:i:s", time()+3600+120). "') AND ".
	       "(`Shifts`.`DateS`<='". gmdate("Y-m-d H:i:s", time()+3600+120+$StartTimeBeforEvent). "') );";
//	"WHERE (Shifts.DateS>'2004-12-27 10:45:00' AND ".
//	       "Shifts.DateS<='2004-12-27 11:00:00');";

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
			
		SetWackeup( $Number, $TimeH, $TimeM);
	}
  }
}

return 0;

?>

