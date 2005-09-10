<?PHP

$title = "Newsverwaltung";
$header = "Verwaltung der News";
include ("./inc/header.php");
include ("./inc/funktion_db_list.php");
include ("./inc/funktion_user.php");


if (!IsSet($action)) {

$SQL = "SELECT * from News order by Datum DESC";
$Erg = mysql_query($SQL, $con);

$rowcount = mysql_num_rows($Erg);
?>
Hallo <? echo $_SESSION['Nick'] ?>, <br>
hier kannst du die News s&auml;bern... falls jemand auf die Idee kommt, 
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
<?				  

for ($i=0; $i < $rowcount; $i++) {
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


} else {

switch ($action) {

case 'change':
	$SQL = "SELECT * from News where (Datum='$date')";
	$Erg = mysql_query($SQL, $con);

	echo "<form action=\"./news.php\" method=\"post\">\n";

	echo "<table>\n";
	echo "  <tr><td>Datum</td><td><input type=\"text\" size=\"40\" name=\"date\" value=\"".mysql_result($Erg, 0, "Datum")."\" disabled></td></tr>\n";
	echo "  <tr><td>Betreff</td><td><input type=\"text\" size=\"40\" name=\"eBetreff\" value=\"".mysql_result($Erg, 0, "Betreff")."\"></td></tr>\n";
	echo "  <tr><td>Text</td><td><textarea rows=\"10\" cols=\"80\" name=\"eText\">".mysql_result($Erg, 0, "Text")."</textarea></td></tr>\n";
	echo "  <tr><td>Engel</td><td><input type=\"text\" size=\"40\" name=\"eUser\" value=\"".UID2Nick(mysql_result($Erg, 0, "UID"))."\" disabled></td></tr>\n";
	echo "  <tr><td>Treffen</td><td><input type=\"text\" size=\"40\" name=\"eTreffen\" value=\"".mysql_result($Erg, 0, "Treffen")."\"></td></tr>\n";
	echo "</table>";

        echo "<input type=\"hidden\" name=\"date\" value=\"$date\">\n";
	echo "<input type=\"hidden\" name=\"action\" value=\"change_save\">\n";
	echo "<input type=\"submit\" value=\"Abschicken...\">\n";
	echo "</form>";

	echo "<form action=\"./news.php?action=delete\" method=\"POST\">\n";
	echo "<input type=\"hidden\" name=\"date\" value=\"$date\">\n";
	echo "<input type=\"submit\" value=\"l&ouml;schen...\">\n";
	echo "</form>";
	

	break;

case 'change_save':
	$chsql="update News set Betreff = '$eBetreff', Text = '$eText', Treffen = '$eTreffen' where (Datum = '$date') limit 1";
	break;

case 'delete':
        $chsql="delete from News where Datum = '$date' limit 1";
	break;
}

if (IsSet($chsql)) {
// SQL-Statement ausführen...
	$Erg = mysql_query($chsql, $con);
	If ($Erg == 1){
		echo "&Auml;nderung erfolgreich gesichert...";
	} else {
		echo "Ein Fehler ist aufgetreten... probiere es am besten nocheinmal... :)";
	}
}

}
include ("./inc/footer.php");
?>

