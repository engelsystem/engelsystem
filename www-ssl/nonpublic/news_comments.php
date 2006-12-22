<?php
$title = "Kommentare zu den News";
$header = "Kommentar";
include ("./inc/header.php");


if( IsSet( $_GET["nid"])) 
{


if( IsSet( $_GET["text"]))
{
	$ch_sql="INSERT INTO `news_comments` (`Refid`, `Datum`, `Text`, `UID`) ".
			"VALUES ('". $_GET["nid"]. "', '". date("Y-m-d H:i:s"). "', '". $_GET["text"]. "', '". $_SESSION["UID"]. "')";
	$Erg = mysql_query($ch_sql, $con);
	if ($Erg == 1)
	{
		echo "Eintrag wurde gespeichert<br><br>"; 
		SetHeaderGo2Back();
	}
}

$SQL = "SELECT * FROM `news_comments` WHERE `Refid`='". $_GET["nid"]. "' ORDER BY 'ID'";
$Erg = mysql_query($SQL, $con);
echo mysql_error( $con);
// anzahl zeilen
$news_rows  = mysql_num_rows($Erg);

?>
<table border="0" width="100%" class="border" cellpadding="2" cellspacing="1">
	<tr class="contenttopic">
		<th width=100 align="left">Datum</th>
		<th align="left">Nick</th>
	</tr>
	<tr class="contenttopic">
		<th align="left" colspan=2>Kommentar</th>
	</tr>

<?PHP
for ($n = 0 ; $n < $news_rows ; $n++) {
  echo "\t<tr class=\"content\">";
  echo "\t\t<td width=100>";
  	echo mysql_result($Erg, $n, "Datum");
  echo "\t\t</td>";
  echo "\t\t<td>";
  	echo UID2Nick(mysql_result($Erg, $n, "UID"));
  	// avatar anzeigen?
  	echo DisplayAvatar (mysql_result($Erg, $n, "UID"));
  echo "\t\t</td>";
  echo "</tr>";
  echo "\t<tr class=\"content\">";
  echo "\t\t<td colspan=\"2\">";
  	echo nl2br(mysql_result($Erg, $n, "Text"))."\n";
  echo "\t\t</td>";
  echo "</tr>";
}

echo "</table>";

?>

<br>
<hr>
<h4>Neuer Kommentar:</h4>
<a name="Neu">&nbsp;</a>

<form action="./news_comments.php" method="GET">
<input type="hidden" name="nid" value="<?PHP echo $_GET["nid"]; ?>">
<table>
 <tr>
  <td align="right" valign="top">Text:</td>
  <td><textarea name="text" cols="50" rows="10"></textarea></td>
 </tr>
</table>
<br>
<input type="submit" value="sichern...">
</form>

<?PHP


} 
else 
{
  echo "Fehlerhafter Aufruf!";
}

include ("./inc/footer.php");
?>
