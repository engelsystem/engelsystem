<?PHP

$title = "Newsverwaltung";
$header = "Verwaltung der News";
include ("../../includes/header.php");
include ("../../includes/funktion_db_list.php");


if (!IsSet($_GET["action"]))
{
	$SQL = "SELECT * FROM `News` ORDER BY `Datum` DESC";
	$Erg = mysql_query($SQL, $con);

	$rowcount = mysql_num_rows($Erg);
	?>
Hallo <?PHP echo $_SESSION['Nick'] ?>, <br>
hier kannst du die News s&auml;ubern... falls jemand auf die Idee kommt, 
hier herumzuspamen oder aus Versehen falsche Informationen zu hinterlegen :)<br><br>

<table width="100%" class="border" cellpadding="2" cellspacing="1">
        <tr class="contenttopic">
         <td>Datum</td>
         <td>Betreff</td>
         <td>Text</td>
         <td>Erfasser</td>
         <td>Engeltreff</td>
	 <td>&Auml;nd.</td>
	</tr>
<?PHP			  

	for ($i=0; $i < $rowcount; $i++) 
	{
		echo "\t<tr class=\"content\">\n";
		echo "\t <td>".mysql_result($Erg, $i, "Datum")."</td>";
		echo "\t <td>".mysql_result($Erg, $i, "Betreff")."</td>";
		echo "\t <td>".mysql_result($Erg, $i, "Text")."</td>";
		echo "\t <td>".UID2Nick(mysql_result($Erg, $i, "UID"))."</td>";
		echo "\t <td>".mysql_result($Erg, $i, "Treffen")."</td>";
		echo "\t <td><a href=\"./news.php?action=change&date=".mysql_result($Erg, $i, "Datum")."\">XXX</a></td>";
		echo "\t</tr>\n";
	}
	echo "</table>";
} 
else 
{

	unSet($chsql);

	switch ($_GET["action"]) 
	{
	case 'change':
		if (isset($_GET["date"]))
		{
			$SQL = "SELECT * FROM `News` WHERE (`Datum`='". $_GET["date"]. "')";
			$Erg = mysql_query($SQL, $con);

			if( mysql_num_rows( $Erg)>0)
			{
				echo "<form action=\"./news.php\" method=\"GET\">\n";

				echo "<table>\n";
				echo "  <tr><td>Datum</td><td><input type=\"text\" size=\"40\" name=\"date\" value=\"".
					mysql_result($Erg, 0, "Datum")."\" disabled></td></tr>\n";
				echo "  <tr><td>Betreff</td><td><input type=\"text\" size=\"40\" name=\"eBetreff\" value=\"".
					mysql_result($Erg, 0, "Betreff")."\"></td></tr>\n";
				echo "  <tr><td>Text</td><td><textarea rows=\"10\" cols=\"80\" name=\"eText\">".
					mysql_result($Erg, 0, "Text")."</textarea></td></tr>\n";
				echo "  <tr><td>Engel</td><td><input type=\"text\" size=\"40\" name=\"eUser\" value=\"".
					UID2Nick(mysql_result($Erg, 0, "UID"))."\" disabled></td></tr>\n";
				echo "  <tr><td>Treffen</td><td><input type=\"text\" size=\"40\" name=\"eTreffen\" value=\"".
					mysql_result($Erg, 0, "Treffen")."\"></td></tr>\n";
				echo "</table>";

			        echo "<input type=\"hidden\" name=\"date\" value=\"". $_GET["date"]. "\">\n";
				echo "<input type=\"hidden\" name=\"action\" value=\"change_save\">\n";
				echo "<input type=\"submit\" value=\"Abschicken...\">\n";
				echo "</form>";

				echo "<form action=\"./news.php?action=delete\" method=\"POST\">\n";
				echo "<input type=\"hidden\" name=\"date\" value=\"". $_GET["date"]. "\">\n";
				echo "<input type=\"submit\" value=\"l&ouml;schen...\">\n";
				echo "</form>";
			}
			else
				echo "FEHLER: Eintrag \"". $_GET["date"]. "\" nicht gefunden";
		}
		else
			echo "Fehler: \"date\" nicht übergeben";
		break;

	case 'change_save':
		if( isset($_GET["date"]) && isset($_GET["eBetreff"]) && isset($_GET["eText"]) )
			$chsql="UPDATE `News` SET `Betreff`='". $_GET["eBetreff"]. "', `Text`='". $_GET["eText"]. 
				"', `Treffen`='". $_GET["eTreffen"]. "' WHERE (`Datum`='". $_GET["date"]. "') limit 1";
		else
			echo "Fehler: nicht genügend parameter übergeben";
		break;

	case 'delete':
		if (isset($_POST["date"]))
		        $chsql="DELETE FROM `News` WHERE `Datum`='". $_POST["date"]. "' LIMIT 1";
		else
			echo "Fehler: \"date\" nicht übergeben";
		break;
	} //SWITCH

	if (IsSet($chsql)) 
	{
		// SQL-Statement ausführen...
		$Erg = mysql_query($chsql, $con);
		If ($Erg == 1)
			echo "&Auml;nderung erfolgreich gesichert...";
		else 
			echo "Ein Fehler ist aufgetreten... probiere es am besten nocheinmal... :)<br><br>\n".
				mysql_error($con). "<br><br>\n[$chsql]";
		SetHeaderGo2Back();
	}
}// IF-ELSE

include ("../../includes/footer.php");
?>

