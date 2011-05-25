<?PHP

$title = "Engelsystem - DECT";
$header = "DECT send call";
include ("../../../camp2011/includes/header.php");

include ("../../../camp2011/includes/config_IAX.php");
//include ("../../../camp2011/includes/funktion_modem.php");
include ("../../../camp2011/includes/funktion_cron.php");

if( !isset($_GET["dial"])) $_GET["dial"] = "";
if( !isset($_GET["custum"])) $_GET["custum"] = "";

if( $_GET["dial"]=="dial")
{
	if( $_GET["DECT"]=="")
		$Number = $_GET["custum"];
	else
		$Number = $_GET["DECT"];
	
	if( strlen( $_GET["timeh"])== 1)
		 $_GET["timeh"] = "0". $_GET["timeh"];
	
	if( strlen( $_GET["timem"])== 1)
		 $_GET["timem"] = "0".  $_GET["timem"];
		
//	SetWackeup( $Number, $_GET["timeh"], $_GET["timem"]);
	DialNumberIAX($Number, $_GET["timeh"], $_GET["timem"],0);

	$_GET["custum"] = $Number;
}


	echo "<form action=\"./dect.php\" method=\"GET\">\n";
	echo "<table>\n";

	echo "<tr><th>Number</th><th>h:m</th><th></th></tr>\n";

	echo "<tr><td>\n";
	// Listet alle Nicks auf
	echo "<select name=\"DECT\">\n";
	echo "\t<option value=\"\">costum</option>\n";

	$usql="SELECT * FROM `User` WHERE NOT `DECT`='' ORDER BY `Nick`";
	$uErg = mysql_query($usql, $con);
	$urowcount = mysql_num_rows($uErg);
	for ($k=0; $k<$urowcount; $k++)
	{
		echo "\t<option value=\"".mysql_result($uErg, $k, "DECT")."\">".
		mysql_result($uErg, $k, "Nick").
		" (". mysql_result($uErg, $k, "DECT"). ")".
		"</option>\n";
	}
	echo "</select>\n";
	
	echo "<input type=\"text\" name=\"custum\" size=\"4\" maxlength=\"4\" value=\"". $_GET["custum"]. "\">\n";
	echo "</td>\n";

	echo "<td><input type=\"text\" name=\"timeh\" size=\"2\" maxlength=\"2\" value=\"". gmdate("H", time()+90+3600). "\">:";
	echo "<input type=\"text\" name=\"timem\" size=\"2\" maxlength=\"2\" value=\"". gmdate("i", time()+90+3600). "\"></td>\n";
	echo "<td><input type=\"submit\" name=\"dial\" value=\"dial\"></td>\n";
	echo "</tr>";
	echo "</table>\n";
	
	echo "</form>";


include ("../../../camp2011/includes/footer.php");
?>

