<?php
$title = "Kommentare zu den News";
$header = "Kommentar";
include ("./inc/header.php");
include ("./inc/db.php");
include ("./inc/funktion_user.php");


if (IsSet($nid)) {


if (IsSet($date) && IsSet($text)){
	
	$ch_sql="INSERT INTO news_comments (Refid, Datum, Text, UID) VALUES ('$nid', '$date', '$text', '".$_SESSION[UID]."')";
	$Erg = mysql_query($ch_sql, $con);
	if ($Erg == 1) { echo "Eintrag wurde gespeichert<br><br>"; }
}

$SQL = "SELECT * FROM news_comments where Refid = $nid ORDER BY 'ID'";
$Erg = mysql_query($SQL, $con);

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

<?
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

<form action="./news_comments.php" method="post">
<input type="hidden" name="date" value="<? echo date("Y-m-d H:i:s"); ?>">
<input type="hidden" name="nid" value="<? echo $nid; ?>">
<table>
 <tr>
  <td align="right" valign="top">Text:</td>
  <td><textarea name="text" cols="50" rows="10"></textarea></td>
 </tr>
</table>
<br>
<input type="submit" value="sichern...">
</form>

<?


} else {

  echo "Fehlerhafter Aufruf!";


   
}
include ("./inc/footer.php");
?>
