<?PHP

$title = "T-Shirt-Ausgabe";
$header = "T-Shirt-Ausgabe f&uuml;r aktiven Engel";
include ("./inc/header.php");
include ("./inc/funktion_db_list.php");


If (IsSet($_GET["aktiv"])) {

	$SQL="UPDATE `User` SET `Tshirt`='1' WHERE `UID`='". $_GET["aktiv"]. "' limit 1";
	$Erg = mysql_query($SQL, $con);
        if ($Erg == 1) {
        } else {
           echo "Fehler beim speichern bei Engel ". UID2Nick($_GET["aktive"]). "<br>";
        }
}

?>

&Uuml;ber die Suchen-Funktion des Browsers kann diese Liste schnell nach einem Nick abgesucht werden.<br>
Hinter diesem erscheint ein Link, &uuml;ber den man eintragen kann, dass der Engel sein T-Shirt erhalten hat.<br><br>

Liste aller aktiven Engel:

<?PHP
$SQL = "SELECT * FROM `User` WHERE (`Aktiv`='1') ORDER BY `Nick` ASC"; 
$Erg = mysql_query($SQL, $con);

$rowcount = mysql_num_rows($Erg);
?>
<table width="100%" class="border" cellpadding="2" cellspacing="1">
        <tr class="contenttopic">
	 <td>Nick</td>
	 <td>Aktiv?</td>
	 <td>Gr&ouml;sse</td>
	 <td>T-Shirt ausgeben:</td>
	</td>
<?PHP
for ($i=0; $i<$rowcount; $i++){
  echo "\t<tr class=\"content\">\n";
    $eUID=mysql_result($Erg, $i, "UID");
    echo "\t\t<td>".UID2Nick($eUID)."</td>\n";
    echo "\t\t<td>".mysql_result($Erg, $i, "Aktiv")."</td>\n";
    echo "\t\t<td>".mysql_result($Erg, $i, "Size")."</td>\n";

    if (mysql_result($Erg, $i, "Tshirt") =="1") {
	echo "\t\t<td>bereits erhalten</td>";
    } else {
	echo "\t\t<td><a href=\"./tshirt.php?aktiv=$eUID\">XXXXXXXX</a></td>";
    }
 echo "\t</tr>\n";
}

echo "</table>";

include ("./inc/footer.php");
?>

