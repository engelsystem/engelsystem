<?PHP

$title = "Defalut User Setting";
$header = "Defalut User Setting";
include ("./inc/header.php");
include ("./inc/funktion_db_list.php");

echo "Hallo ".$_SESSION['Nick'].
	",<br>\nhier hast du die M&ouml;glichkeit, die Defaulteinstellungen f&uuml;r neue User einzustellen:<br><br>\n";
				
echo "<table border=\"0\" class=\"border\">\n";
echo "\t<tr class=\"contenttopic\">\n";
echo "\t\t<th>Page</th>\n\t\t<th>Show</th>\n\t\t<th></th>\n";
echo "\t</tr>\n";

if( isset( $_GET["Field"]) && isset( $_GET["Default"]) && isset( $_GET["Send"]))
{
	switch( $_GET["Send"])
	{
		case "New":
 			$SQL = "ALTER TABLE `UserCVS` ADD `". $_GET["Field"]. "` ".
				"CHAR( 1 ) DEFAULT '". $_GET["Default"]. "' NOT NULL";
			$Erg = mysql_query( $SQL, $con);
			if( $Erg == 1)
				echo "<H2>Create ".$_GET["Field"]. " = ". $_GET["Default"]. " succesfull</h2>\n";
			else
				echo "<H2>Create ".$_GET["Field"]. " = ". $_GET["Default"]. " error...</h2>\n".
					"[". mysql_error(). "]<br><br>";
			break;
		case "Del":
			echo "\t<tr class=\"content\">\n";
			echo "\t\t<form action=\"userDefaultSetting.php\">\n";
			echo "\t\t\t<td><input name=\"Field\" type=\"text\" value=\"". $_GET["Field"]. "\" readonly></td>\n";
			echo "\t\t\t<td><input name=\"Default\" type=\"text\" value=\"". $_GET["Default"]. "\" readonly></td>\n";
			echo "\t\t\t<td><input type=\"submit\" name=\"Send\" value=\"Del sure\"></td>\n";
			echo "\t\t</form>\n";
			echo "\t</tr>\n";
			break;
		case "Del sure":
			$SQL = "ALTER TABLE `UserCVS` DROP `". $_GET["Field"]. "` ";
			$Erg = mysql_query( $SQL, $con);
			if( $Erg == 1)
				echo "<H2>Delete ".$_GET["Field"]. " succesfull</h2>\n";
			else
				echo "<H2>Delete ".$_GET["Field"]. " error...</h2>\n".
					"[". mysql_error(). "]<br><br>";
			break;
		case "SetForAllUser":
			$SQL = "UPDATE `UserCVS` SET `". $_GET["Field"]. "`='". $_GET["Default"]. "'";
			$Erg = mysql_query( $SQL, $con);
			if( $Erg == 1)
				echo "<H2>UPDATE ".$_GET["Field"]. " = ". $_GET["Default"]. " for all Users succesfull</h2>\n";
			else
				echo "<H2>UPDATE ".$_GET["Field"]. " = ". $_GET["Default"]. " for all Users error...</h2>\n".
					"[". mysql_error(). "]<br><br>";
		case "Save":
			$SQL = "ALTER TABLE `UserCVS` CHANGE `". $_GET["Field"]. "` ".
				"`". $_GET["Field"]. "` CHAR( 1 ) NOT NULL DEFAULT '". $_GET["Default"]. "'";
			$Erg = mysql_query( $SQL, $con);
			if( $Erg == 1)
				echo "<H2>Write ".$_GET["Field"]. " = ". $_GET["Default"]. " succesfull</h2>\n";
			else
				echo "<H2>Write ".$_GET["Field"]. " = ". $_GET["Default"]. " error...</h2>\n".
					"[". mysql_error(). "]<br><br>";
			break;
	} //SWITCH
} //IF(


$erg = mysql_query("SHOW COLUMNS FROM `UserCVS`");
echo mysql_error();

for( $i=1; $i<mysql_num_rows($erg); $i++)
{
	echo "\t<tr class=\"content\">\n";
	echo "\t\t<form action=\"userDefaultSetting.php\">\n";
	echo "\t\t\t<input name=\"Field\" type=\"hidden\" value=\"". mysql_result( $erg, $i, "Field"). "\">\n";
	echo "\t\t\t<td>". mysql_result( $erg, $i, "Field"). "</td>\n";
	echo "\t\t\t<td>";
	if( mysql_result( $erg, $i, "Default") == "Y")
		echo	"<input type=\"radio\" name=\"Default\" value=\"Y\" checked>Y\n".
			"\t\t\t    <input type=\"radio\" name=\"Default\" value=\"N\">N";
	else
		echo	"<input type=\"radio\" name=\"Default\" value=\"Y\">Y\n".
			"\t\t\t    <input type=\"radio\" name=\"Default\" value=\"N\" checked>N";
	echo "</td>\n";
	echo "\t\t\t<td><input type=\"submit\" name=\"Send\" value=\"Save\">\n";
	echo "\t\t\t    <input type=\"submit\" name=\"Send\" value=\"Del\">\n";
	echo "\t\t\t    <input type=\"submit\" name=\"Send\" value=\"SetForAllUser\"></td>\n";
	echo "\t\t</form>\n";
	echo "\t</tr>\n";
}
	
echo "\t<tr class=\"content\">\n";
echo "\t\t<form action=\"userDefaultSetting.php\">\n";
echo "\t\t\t<input name=\"New\" type=\"hidden\" value=\"New\">\n";
echo "\t\t\t<td><input name=\"Field\" type=\"text\" value=\"new\"></td>\n";
echo "\t\t\t<td><input type=\"radio\" name=\"Default\" value=\"Y\">Y\t".
	"\t\t\t\t<input type=\"radio\" name=\"Default\" value=\"N\">N</td>\n";
echo "\t\t\t<td><input type=\"submit\" name=\"Send\" value=\"New\"></td>\n";
echo "\t\t</form>\n";
echo "\t</tr>\n";



echo "</table>\n";

include ("./inc/footer.php");
?>

