<?php
$title = "Himmel";
$header = "FAQ / Fragen an die Erzengel";
include ("./inc/header.php");


// Erstaufruf, oder Frage bereits abgeschickt?
if (!IsSet($_POST["eUID"])) 
{
	Print_Text(35);
?>
<br><br>
<form action="./faq.php" method="POST">
  <input type="hidden" name="eUID" value="<? echo $_SESSION['UID'] ?>">
  <textarea name="frage"  cols="40" rows="10"><?PHP Print_Text(36); ?></textarea><br><br>
  <input type="submit" value="<?PHP Print_Text("save"); ?>">
</form>
<?

} else {
// Auswertung d. Formular-Daten:

echo "<b>".Get_Text(37)."</b><br><br>\n".nl2br($_POST["frage"])."<br><br>\n".Get_Text(38)."<br>\n";

$SQL = "INSERT into Questions VALUES (\"\", \"".$_SESSION['UID']."\", \"". $_POST["frage"]. "\", \"\", \"\")";
$Erg = mysql_query($SQL, $con);

}
// Bisherige Anfragen:
echo "<br>\n<b>".Get_Text(39)."</b><br>\n";
echo "<hr width=\"99%\">\n";
echo "<br><b>".Get_Text(40)."</b><br>\n";

$SQL = "SELECT * from Questions where UID = ".$_SESSION['UID']." and AID=\"0\" ORDER BY 'QID' DESC";
$Erg = mysql_query($SQL, $con);

// anzahl zeilen
$Zeilen  = mysql_num_rows($Erg);

if ($Zeilen==0){
	Print_Text(41);

} else {
	for ($n = 0 ; $n < $Zeilen ; $n++) {
	  echo "<p class='question'>".nl2br(mysql_result($Erg, $n, "Question"))."<br>\n";
// Es gibt ja noch keine Antwort:
//	  echo "<p class='answer'>".nl2br(mysql_result($Erg, $n, "Answer"))."</p>\n";
	  echo "\n<br>---<br>";
	}
}

echo "<hr width=\"99%\">\n";
echo "<br><b>".Get_Text(42)."</b><br>\n";
$SQL = "SELECT * from Questions where UID = ".$_SESSION['UID']." and AID<>\"0\" ORDER BY 'QID' DESC";
$Erg = mysql_query($SQL, $con);

// anzahl zeilen
$Zeilen  = mysql_num_rows($Erg);

if ($Zeilen==0){
	Print_Text(41);
} else {
	for ($n = 0 ; $n < $Zeilen ; $n++) {
	  echo "<p class='question'>".nl2br(mysql_result($Erg, $n, "Question"))."<br>\n";
	  echo "<p class='answer'>".nl2br(mysql_result($Erg, $n, "Answer"))."\n";
	  echo "\n<br>---<br>";
	}
}

include ("./inc/footer.php");
?>
