<?PHP

$title = "Defalut User Setting";
$header = "Defalut User Setting";
include ("./inc/header.php");
include ("./inc/funktion_db_list.php");

echo "Hallo ".$_SESSION['Nick'].
	",<br>\nhier hast du die M&ouml;glichkeit, die Defaulteinstellungen f&uuml;r neue User einzustellen:<br><br>\n";
				

if( isset( $_GET["Field"]) && isset( $_GET["Default"]))
{
	$SQL = "ALTER TABLE `UserCVS` CHANGE `". $_GET["Field"]. "` ".
		"`". $_GET["Field"]. "` CHAR( 1 ) NOT NULL DEFAULT '". $_GET["Default"]. "'";
	$erg = mysql_query( $SQL, $con);
	if( $erg == 1)
		echo "<H2>Write ".$_GET["Field"]. " = ". $_GET["Default"]. " succesfull</h2>\n";
	else
		echo "<H2>Write ".$_GET["Field"]. " = ". $_GET["Default"]. " error...</h2>\n".
			"[". mysql_error(). "]<br><br>";
}

$erg = mysql_query("SHOW COLUMNS FROM `UserCVS`");
echo mysql_error();

echo "<table border=\"0\" class=\"border\">\n";
echo "\t<tr class=\"contenttopic\">\n";
echo "\t\t<th>Page</th>\n\t\t<th>Show</th>\n\t\t<th></th>\n";
echo "\t</tr>\n";
for( $i=1; $i<mysql_num_rows($erg); $i++)
{
	echo "\t<tr class=\"content\">\n";
	echo "\t\t<form action=\"userDefaultSetting.php\">\n";
	echo "\t\t\t<input name=\"Field\" type=\"hidden\" value=\"". mysql_result( $erg, $i, "Field"). "\">\n";
	echo "\t\t\t<td>". mysql_result( $erg, $i, "Field"). "</td>\n";
	echo "\t\t\t<td>";
	if( mysql_result( $erg, $i, "Default") == "Y")
		echo	"<input type=\"radio\" name=\"Default\" value=\"Y\" checked>Y\t".
			"\t\t\t\t<input type=\"radio\" name=\"Default\" value=\"N\">N";
	else
		echo	"<input type=\"radio\" name=\"Default\" value=\"Y\">Y\t".
			"\t\t\t\t<input type=\"radio\" name=\"Default\" value=\"N\" checked>N";
	echo "</td>\n";
	echo "\t\t\t<td><input type=\"submit\" value=\"Save\"></td>\n";
	echo "\t\t</form>\n";
	echo "\t</tr>\n";
}
echo "</table>\n";

include ("./inc/footer.php");
?>

