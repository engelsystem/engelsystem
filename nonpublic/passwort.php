<?php
$title = "Himmel";
$header = "News";
include ("./inc/header.php");

if (!IsSet($action)) {

?>

Hier kannst du dein Kennwort f&uuml;r unsere Himmelsverwaltung &auml;ndern. <br><br>

<form action="./passwort.php" method="post">
	<input type="hidden" name="action" value="set">
	<table>
	  <tr><td>Altes Passwort:</td><td><input type="password" name="old" size="20"></td></tr>
	  <tr><td>Neues Passwort:</td><td><input type="password" name="new1" size="20"></td></tr>
	  <tr><td>Passwortbest&auml;tigung:</td><td><input type="password" name="new2" size="20"></td></tr>
	</table>
	<input type="submit" value="Abschicken...">
</form>
<?

} else {

	if ($action == "set") {
		if ($new1==$new2){
			echo "Eingegebene Kennw&ouml;rter sind nicht gleich. -> ok.<br>";
			echo "Check, ob altes Passwort ok ist.";
			$sql = "select * from User where UID=".$_SESSION['UID'];
			$Erg = mysql_query($sql, $con);
			if (md5($old)==mysql_result($Erg, $i, "Passwort")) {
				echo "-> ok.<br>";
				echo "Setzen des neuen Kennwortes...: ";
				$usql = "update User set Passwort='".md5($new1)."' where UID=".$_SESSION['UID'];
				$Erg = mysql_query($usql, $con);
				if ($Erg==1) {
					echo "Neues Kennwort wurde gesetzt.";
				} else {
					echo "Ein Fehler ist trotzdem noch aufgetreten. Probiere es einfach nocheinmal :)";
				}

				
			} else {
				echo "-> nicht ok.<br>";
				echo "Altes Kennwort ist nicht ok. Bitte wiederholen.<br>";
			}
			
		} else {
			echo "Kennw&ouml;rter sind nicht gleich. Bitte wiederholen.";
		}
	
	} else {
		echo "Ung&uuml;ltiger Aufruf!\n";
	}
}

include ("./inc/footer.php");
?>
