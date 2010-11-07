<?PHP

$title = "Erzengel";
$header = "Freie Engel";
include ("../../includes/header.php");
include ("../../includes/funktion_db_list.php");


echo "Hallo ".$_SESSION['Nick'].",<br>\n";

echo "<br><br>\n\nHier findest du alle Engel, welche zur Zeit in keiner Schicht verplant sind:<br><br>\n";


#######################################################
# Ermitteln freier Engel
# 
# auslesen aller Engel und dazugehoerige Schichten
#######################################################

// $SQL= "SELECT User.Nick, Schichtplan.*, Schichtbelegung. * FROM User LEFT JOIN Schichtplan ON User.UID=Schichtbelegung.UID, Schichtplan.SID LEFT JOIN Schichtbelegung.SID WHERE User.UID = Schichtbelegung.UID AND Schichtplan.SID = Schichtbelegung.SID AND Schichtplan.Date < now() and Schichtplan.EndDate > now() ORDER BY Nick";

/* geht nicht ??? unter stabel !!
$SQL= "SELECT User.Nick, Schichtplan.*, Schichtbelegung.* ".
	"FROM Schichtplan, User LEFT OUTER ".
	"JOIN Schichtbelegung ON User.UID=Schichtbelegung.UID ".
	"WHERE Schichtplan.SID = Schichtbelegung.SID AND ".
		"Schichtplan.Date < now() and ".
		"Schichtplan.EndDate > now() ".
	"ORDER BY Nick";
	
$SQL =  "SELECT Shifts.*, ShiftEntry.*, User.Nick ".
	"FROM User ".
	"INNER JOIN (Shifts INNER JOIN ShiftEntry ON Shifts.SID = ShiftEntry.SID) ON User.UID = ShiftEntry.UID ".
	"WHERE (Shifts.DateS<=Now() AND Shifts.DateE>=Now() );";		
*/
$SQL =  "SELECT Shifts.*, ShiftEntry.* ".
	"FROM `Shifts` INNER JOIN ShiftEntry ON Shifts.SID = ShiftEntry.SID ".
	"WHERE (Shifts.DateS<=Now() AND Shifts.DateE>=Now() AND  ShiftEntry.UID>0);";		

//SELECT User.Nick, Schichtplan.*, Schichtbelegung. * FROM User LEFT JOIN Schichtbelegung ON User.UID=Schichtbelegung.UID, Schichtplan LEFT JOIN Schichtbelegung ON Schichtplan.SID = Schichtbelegung.SID WHERE Schichtplan.Date < now() and Schichtplan.EndDate > now() ORDER BY Nick

//echo "<pre>$SQL</pre>"; 

$Erg = mysql_query($SQL, $con);
$Zeilen  = mysql_num_rows($Erg);

// for ($i = 1; $i < mysql_num_fields($Erg); $i++)
//  echo "|".mysql_field_name($Erg, $i);



echo "<table width=\"100%\" class=\"border\" cellpadding=\"2\" cellspacing=\"1\">\n";
echo "\t<tr class=\"contenttopic\">\n";
echo "\t\t<td>Nick</td>\n";
echo "\t\t<td>Schicht</td>\n";
echo "\t\t<td>Ort</td>\n";
echo "\t\t<td>Von</td>\n";
echo "\t\t<td>Bis</td>\n";
echo "\t</tr>\n";


$inuse="";
for ($i=0; $i < $Zeilen; $i++)
{
	echo "<tr class=\"content\">\n";
	echo "<td><a href=\"./userChangeNormal.php?Type=Normal&enterUID=". mysql_result($Erg, $i, "UID"). "\">". 
		UID2Nick(mysql_result($Erg, $i, "UID")). "</td></a>\n";
	echo "<td></td>\n";
	echo "<td>". mysql_result($Erg, $i, "RID"). "</td>\n";
	echo "<td>". mysql_result($Erg, $i, "DateS"). "</td>\n";
	echo "<td>". mysql_result($Erg, $i, "DateE"). "</td>\n";
	echo "</tr>\n";

	if ($inuse!="") 
  		$inuse.= " OR ";
	$inuse.= "(Nick = \"". UID2Nick(mysql_result($Erg, $i, "UID")). "\")";
}
if ($inuse!="") 
	$inuse=" WHERE NOT (".$inuse.")";
echo "</table>\n";


//##########################################################################################################

echo "<br><br>\n\nhier findest du alle Engel, welche zur Zeit in keiner Schichten verplant sind:<br><br>\n";
echo "<table width=\"100%\" class=\"border\" cellpadding=\"2\" cellspacing=\"1\"\>\n";
echo "\t<tr class=\"contenttopic\">\n\t\t<td>Nick</td>\n\t\t<td>DECT</td>\n\t</tr>\n";

$SQL = "SELECT Nick, UID, DECT FROM User".$inuse.";";
$Erg = mysql_query($SQL, $con);
$Zeilen  = mysql_num_rows($Erg);
for ($i=0; $i < $Zeilen; $i++)
{
	echo "\t<tr class=\"content\">\n";
	echo "\t\t<td><a href=\"./userChangeNormal.php?Type=Normal&enterUID=". mysql_result($Erg, $i, "UID"). "\">".
		mysql_result($Erg, $i, "Nick"). "</a></td>\n";
	echo "\t\t<td>". mysql_result($Erg, $i, "DECT"). "</td>\n";
 	echo "\n</tr>\n";
}
echo "</table>\n";

include ("../../includes/footer.php");
?>

