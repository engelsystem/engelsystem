<?PHP
$title = "DB Update from XML";
$header = "DB Update from XML";
include ("./inc/header.php");
include ("./inc/funktion_user.php");
include ("./inc/funktion_xml.php");

///////////
// DEBUG //
///////////
$ShowDataStrukture = 0;
$EnableRoomFunctions = 1;
$EnableRooms = 1;
$EnableRoomsDB = 1;
$EnableSchudleFunctions = 1;
$EnableSchudle = 1;
$EnableSchudleDB = 1;


/*##############################################################################################
				erstellt Arrays der Reume
  ##############################################################################################*/
function CreateRoomArrays()
{
	global $Room, $RoomID, $RoomName, $con;

	$sql =	"SELECT `RID`, `Name` FROM `Room` ".
		"WHERE `Show`='Y'".
		"ORDER BY `Number`, `Name`;";
	$Erg = mysql_query($sql, $con);
	$rowcount = mysql_num_rows($Erg);

	for ($i=0; $i<$rowcount; $i++)
	{
		$Room[$i]["RID"]  = mysql_result($Erg, $i, "RID");
		$Room[$i]["Name"] = mysql_result($Erg, $i, "Name");
		$RoomID[ mysql_result($Erg, $i, "RID") ] =  mysql_result($Erg, $i, "Name");
		$RoomName[ mysql_result($Erg, $i, "Name") ] = mysql_result($Erg, $i, "RID");
	}
}
CreateRoomArrays();

/*##############################################################################################
				           F I L E
  ##############################################################################################*/
echo "\n\n<br>\n<h1>XML File:</h1>\n";
if( isset($_POST["PentabarfUser"]) && isset($_POST["PentabarfPasswd"]) && isset($_POST["PentabarfURL"]))
{
	echo "Update XML-File from Pentabarf..";
	
	if( $DataGetMeth=="wget")
		$Command = "wget --http-user=". $_POST["PentabarfUser"]. " --http-passwd=".$_POST["PentabarfPasswd"]. " ".
				$_POST["PentabarfURL"].
				" --output-file=/tmp/engelXMLwgetLog --output-document=/tmp/engelXML".
				" --no-check-certificate";
	elseif( $DataGetMeth=="lynx")
		$Command = "lynx -auth=". $_POST["PentabarfUser"]. ":".$_POST["PentabarfPasswd"]. " -dump ".
				$_POST["PentabarfURL"].	" > /tmp/engelXML";
	echo system( $Command, $Status);

	if( $Status==0)
		echo "OK.<br>";
	else
		echo "fail ($Status)($Command).<br>";
}
else
{
	echo "<form action=\"dbUpdateFromXLS.php\" method=\"post\">\n";
	echo "<table border=\"0\">\n";
	echo "\t<tr><td>XML-File:</td>".
		"<td><input name=\"PentabarfURL\" type=\"text\" size=\"100\" maxlength=\"1000\" ".
		"value=\"https://pentabarf.cccv.de/~sven/xcal/conference/7\"></td></tr>\n";
	echo "\t<tr><td>Username:</td>".
		"<td><input name=\"PentabarfUser\" type=\"text\" size=\"30\" maxlength=\"30\"></td></tr>\n";
	echo "\t<tr><td>Password:</td>".
		"<td><input name=\"PentabarfPasswd\" type=\"password\" size=\"30\" maxlength=\"30\"></td></tr>\n";
	echo "\t<tr><td></td><td><input type=\"submit\" name=\"FileUpload\" value=\"upload\"></td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";
}



//readXMLfile("xml.php.xml");
if( readXMLfile("/tmp/engelXML") == 0)
{
$XMLmain = getXMLsubPease( $XMLmain, "VCALENDAR");


if( $ShowDataStrukture)
{
	echo "<pre><br>";
	echo $XMLmain->name;
	echo "<br>";
	print_r(array_values ($XMLmain->sub));
	echo "</pre>";
}

/*
echo "<br>";
$Feld=7;
echo "$Feld#". $XMLmain->sub[$Feld]->name. "<br>";
echo "$Feld#". $XMLmain->sub[$Feld]->sub;
//print_r(array_values ($XMLmain->sub[$Feld]->sub));
while(list($key, $value) = each($XMLmain->sub[$Feld]->sub))
	echo "?ID".$value->sub[1]->data. "=". $value->sub[2]->data. "\n";
echo "</pre>";
*/


/*##############################################################################################
				           V e r s i o n
  ##############################################################################################*/

echo "<hr>\n";
$XMLrelease = getXMLsubPease( $XMLmain, "X-WR-CALDESC");
echo "release: ". $XMLrelease->data. "<br>\n";
//$XMLreleaseDate = getXMLsubPease( $XMLmain, "RELEASE-DATE");
//echo "release date: ". $XMLreleaseDate->data. "<br>\n";
echo "<hr>\n";



/*##############################################################################################
				           V e r s i o n
  ##############################################################################################*/
if( $EnableRoomFunctions)
	include("./inc/funktion_xml_room.php");

if( $EnableSchudleFunctions)
	include("./inc/funktion_xml_schudle.php");


/*##############################################################################################
				         U P D A T E  A L L 
  ##############################################################################################*/
echo "\n\n<br>\n<h1>Update ALL:</h1>\n";

echo "<form action=\"dbUpdateFromXLS.php\">\n";
echo "\t<input type=\"submit\" name=\"UpdateALL\" value=\"now\">\n";
echo "</form>\n";

} //if XMLopenOOK

include ("./inc/footer.php");
?>

