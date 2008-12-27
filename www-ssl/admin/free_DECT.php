<?PHP

include ("../../includes/config_db.php");
include ("../../includes/funktion_db_list.php");


#######################################################
# Ermitteln freier Engel
# 
# auslesen aller Engel und dazugehoerige Schichten
#######################################################

$SQL =  "SELECT Shifts.*, ShiftEntry.* ".
	"FROM `Shifts` INNER JOIN ShiftEntry ON Shifts.SID = ShiftEntry.SID ".
	"WHERE (Shifts.DateS<=Now() AND Shifts.DateE>=Now() AND  ShiftEntry.UID>0);";		

$Erg = mysql_query($SQL, $con);
$Zeilen  = mysql_num_rows($Erg);

$inuse="";
for ($i=0; $i < $Zeilen; $i++)
{
	if ($inuse!="") 
  		$inuse.= " OR ";
	$inuse.= "(UID = \"". mysql_result($Erg, $i, "UID"). "\")";
}
if ($inuse!="") 
	$inuse=" WHERE (NOT (".$inuse.")) AND (DECT!='')";


//##########################################################################################################

$SQL = "SELECT * FROM User".$inuse.";";
$Erg = mysql_query($SQL, $con);
$Zeilen  = mysql_num_rows($Erg);
for ($i=0; $i < $Zeilen; $i++)
{
	$SQL_RIGHT = "SELECT * FROM UserCVS WHERE UID=". mysql_result($Erg, $i, "UID"). ";";
	$Erg_RIGHT = mysql_query($SQL_RIGHT, $con);

	echo mysql_result($Erg, $i, "DECT"). "\t";
	echo "Info=". mysql_result($Erg_RIGHT, 0, "Info"). "\t";
	echo "Herald=". mysql_result($Erg_RIGHT, 0, "Herald"). "\t";
	echo "Conference=". mysql_result($Erg_RIGHT, 0, "Conference"). "\t";
	
	echo "\n";
}

?>

