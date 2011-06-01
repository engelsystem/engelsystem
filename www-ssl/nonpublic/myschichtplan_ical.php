<?php
include ("../../../camp2011/includes/header_start.php");

include ("../../../camp2011/includes/funktion_schichtplan_aray.php");

$SQL = "SELECT *, `ShiftEntry`.`Comment`, `ShiftEntry`.`TID` FROM `Shifts` ".
       "INNER JOIN `ShiftEntry` ".
       "ON `Shifts`.`SID`=`ShiftEntry`.`SID` ".
       "WHERE `ShiftEntry`.`UID`='". $_SESSION['UID']. "' ".
       "ORDER BY `DateS`";
$erg = mysql_query($SQL, $con);


//HEADER
header("Content-Type: text/x-vCalendar");
header("Content-Disposition: attachment; filename=\"Schichtplan.ics\"" );

//DATA
echo "BEGIN:VCALENDAR\n";
echo "PRODID:-//Engelsystem//DE-EN\n";
echo "VERSION:2.0\n";
echo "PRODID:". md5('icalschichtplan:'.$_SESSION['UID']). "\n"; 
echo "METHOD:PUBLISH\n";
echo "CALSCALE:GREGORIAN\n";
echo "METHOD:PUBLISH\n";
echo "X-WR-CALNAME;VALUE=TEXT:". "Himmel - Schichtplan\n";

for( $i=0; $i<mysql_num_rows( $erg ); $i++ ) 
{
  echo "BEGIN:VEVENT\n";
  echo "UID:". md5(mysql_result( $erg, $i, "Man" ). mysql_result( $erg, $i, "DateS" ))."\n";
  echo "METHOD:PUBLISH\n";
  echo "DTSTART;TZID=Europe/Berlin:". date( 'Ymd\THis', strtotime( mysql_result( $erg, $i, "DateS" ) ) ). "\n";
  echo "DTEND;TZID=Europe/Berlin:". date( 'Ymd\THis', strtotime( mysql_result( $erg, $i, "DateE" ) ) ). "\n";
  echo "SUMMARY:". str_replace( ',', '\\,',mysql_result( $erg, $i, "Man" ) ). "\n";
  echo "CLASS:PUBLIC\n";
  echo "STATUS:CONFIRMED\n";
  echo "URL:". $url. $ENGEL_ROOT. "nonpublic/myschichtplan.php\n";
  echo "LOCATION:". $RoomID[mysql_result( $erg, $i, "RID" )]. "\n";
  echo "BEGIN:VALARM\n";
  echo "TRIGGER;VALUE=DURATION:-PT5M\n";
  echo "DESCRIPTION:". str_replace( ',', '\\,',mysql_result( $erg, $i, "Man" ) ). "\n";
  echo "ACTION:DISPLAY\n";
  echo "END:VALARM\n";
  echo "END:VEVENT\n";
}
echo "END:VCALENDAR\n";

include( "../../../camp2011/includes/funktion_counter.php");

?>

