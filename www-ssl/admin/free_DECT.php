<?PHP

include ("../../includes/config_db.php");
include ("../../includes/funktion_db_list.php");
include ("../../includes/funktion_user.php");


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
{
	$inuse=" WHERE (NOT (".$inuse.")) AND (DECT!='')";
}
else
{
	$inuse=" WHERE (DECT!='')";
}


//##########################################################################################################

$SQL = "SELECT * FROM User".$inuse.";";
$Erg = mysql_query($SQL, $con);
$Zeilen  = mysql_num_rows($Erg);
for ($i=0; $i < $Zeilen; $i++)
{
	// get DECT number
	echo mysql_result($Erg, $i, "DECT"). "\t";

	// get all user rights
	$SQL_RIGHT = "SELECT * FROM UserCVS WHERE UID=". mysql_result($Erg, $i, "UID"). ";";
	$Erg_RIGHT = mysql_query($SQL_RIGHT, $con);
	$UserRights = mysql_fetch_array($Erg_RIGHT);

	foreach( $UserRights as $Var => $Value)
	{
		if( 	(strpos( $Var, ".php") === false) AND 
			(strpos( $Var, "/") === false) AND
			(strpos( $Var, "UID") === false) AND
			(is_numeric($Var) === false) )
		{
			echo "\"".$Var. "\"=". $Value. "\t";
		}
	}

	// get shift types
	$SQL_TYPES = "SELECT TID FROM `ShiftEntry` WHERE UID=". mysql_result($Erg, $i, "UID"). " GROUP BY TID;";
	$Erg_TYPES = mysql_query($SQL_TYPES, $con);
	$Zeilen_Typen  = mysql_num_rows($Erg_TYPES);
	for ($j=0; $j < $Zeilen_Typen; $j++)
	{
		echo "\"TID_". TID2Type( mysql_result($Erg_TYPES, $j, "TID")). "\"=Y\t";	
	}

	echo "\n";
//	echo "<br>";
}

?>

