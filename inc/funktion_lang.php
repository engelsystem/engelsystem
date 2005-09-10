<?PHP

function Get_Text ($TextID)
{
if ($_SESSION['Sprache']=="") $_SESSION['Sprache']="EN";

GLOBAL $con;
$SQL = "SELECT * FROM `Sprache` WHERE TextID=\"$TextID\" AND Sprache ='".$_SESSION['Sprache']."'";
@$Erg = mysql_query($SQL, $con);
if(!mysql_error($con))
	return (@mysql_result($Erg, 0, "Text"));
else
	return "Error Data";
	
}

function Print_Text ($TextID){
GLOBAL $con;

$SQL = "SELECT * FROM `Sprache` WHERE TextID=\"$TextID\" AND Sprache ='".$_SESSION['Sprache']."'";
@$Erg = mysql_query($SQL, $con);

if(!mysql_error($con))
	echo nl2br(@mysql_result($Erg, 0, "Text"));
else
	echo "Error Data";
}

?>
