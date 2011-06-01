<?php
require_once ('../bootstrap.php');

$title = "Himmel";
$header = "FAQ / Fragen an die Erzengel";
$submenus = 1;
include ("includes/header.php");
include ("includes/funktion_db.php");

//var init
$quest_bearb = 0;

if (IsSet ($_GET["quest"])) {

	switch ($_GET["quest"]) {

		// *---------------------------------------------------------------------------
		// * Anfragen - Bearbeitung
		// *---------------------------------------------------------------------------
		// * je nach �bergabeoption ($quest) koennen Anfragen beantwortet werden oder
		// * als FAQ uebergeben werden 
		// *---------------------------------------------------------------------------

		case "all" :
			$SQL = "SELECT * FROM `Questions` ORDER BY QID DESC";
?>
  Alle Anfragen:<br />
        <table  width="100%" class="border" cellpadding="2" cellspacing="1">
                <tr class="contenttopic">
      <th>Frage</th>
      <th>Anfragender</th>
                        <th>Beantwortet?</th>
                        <th>Antwort</th>
                        <th>Antwort von</th>
      <th>change</th>
                </tr>

<?php


			$Erg = mysql_query($SQL, $con);
			// anzahl zeilen
			$Zeilen = mysql_num_rows($Erg);
			for ($n = 0; $n < $Zeilen; $n++) {
				echo "<tr class=\"content\">\n";
				echo "<td>" . mysql_result($Erg, $n, "Question") . "</td>\n";
				echo "<td>" . UID2Nick(mysql_result($Erg, $n, "UID")) . "</td>\n";
				echo "<td>";
				if (mysql_result($Erg, $n, "AID") > 0) {
					echo "Ja</td>\n";
					echo "<td>" . mysql_result($Erg, $n, "Answer") . "</td>\n";
					echo "<td>" . UID2Nick(mysql_result($Erg, $n, "AID")) . "</td>\n";
				} else {
					echo "Nein</td>\n";
					echo "<td>&nbsp;</td>\n";
					echo "<td>&nbsp;</td>\n";
				}
				echo "<td><a href=\"faq.php?quest=edit&QID=" . mysql_result($Erg, $n, "QID") . "\">xxx</a></td>";
				echo "</tr>\n";
			}
			echo "</table>\n";
			break;

		case "open" :
			$SQL = "SELECT * FROM `Questions` WHERE `AID`='0' ORDER BY `QID` DESC";
			$quest_bearb = 1; // Fragenliste anzeigen
			echo "\t\tOffene Anfragen:<br />\n";
			break;

		case "edit" :
			if (!IsSet ($_GET["QID"]))
				echo "\t\tFehlerhafter Aufruf...<br />Bitte die Bearbeitung nochmals beginnen :)\n";
			else {
				$SQL = "SELECT * FROM `Questions` WHERE `QID`='" . $_GET["QID"] . "'";
				$Erg = mysql_query($SQL, $con);
				echo "\t\t<form action=\"./faq.php\" method=\"GET\">\n";
				echo "\t\tAnfrage von <b>" . UID2NICK(mysql_result($Erg, 0, "UID")) . "</b>:<br />\n";
				echo "\t\t<textarea name=\"Question\" rows=\"3\" cols=\"80\">" .
				mysql_result($Erg, 0, "Question") . "</textarea>\n";
				echo "<br /><br />Antwort der Erzengel:<br />\n";
				if (mysql_result($Erg, 0, "Answer") == "")
					echo "\t\t<textarea name=\"Answer\" rows=\"5\" cols=\"80\">" .
					"Bitte hier die Antwort eintragen...</textarea><br />\n";
				else
					echo "\t\t<textarea name=\"Answer\" rows=\"5\" cols=\"80\">" .
					mysql_result($Erg, 0, "Answer") . "</textarea>\n<br />\n";
				echo "\t\t<input type=\"hidden\" name=\"QID\" value=\"" . $_GET["QID"] . "\">\n";
				echo "\t\t<input type=\"hidden\" name=\"quest\" value=\"save\">\n";
				echo "\t\t<input type=\"submit\" value=\"Sichern...\">\n";
				echo "\t</form>\n";
				if (mysql_result($Erg, 0, "AID") <> "0") {
					echo "\tDu kannst diese Anfrage so wie sie ist, als Engel-FAQ eintrag &uuml;bernehmen.<br />\n";
					echo "<a href=\"./faq.php?quest=transfer&QID=" . $_GET["QID"] . "\">Als FAQ-Eintrag sichern...</a>\n";
				}
			} // Abfrage der QID
			break;

		case "save" :
			if (!IsSet ($_GET["QID"]))
				echo "\tFehlerhafter Aufruf... Bitte die Bearbeitung nochmal starten...";
			else {
				$SQL = "UPDATE `Questions` SET `Question`='" . $_GET["Question"] .
				"', `AID`='" . $_SESSION['UID'] . "' , `Answer`='" . $_GET["Answer"] . "' " .
				"WHERE `QID`='" . $_GET["QID"] . "' LIMIT 1";
				$Erg = db_query($SQL, "save Question");
				if ($Erg == 1) {
					echo "\tDer Eintrag wurde ge&auml;ndert<br />\n";
					SetHeaderGo2Back();
				} else
					echo "\tEin Fehler ist aufgetreten. Sorry, du kannst es aber ja nochmal probieren :)\n";
			}
			break;

		case "transfer" :
			if (!IsSet ($_GET["QID"]))
				echo "\tFehlerhafter Aufruf... Bitte die Bearbeitung nochmal starten...\n";
			else {
				$SQL1 = "SELECT * FROM `Questions` WHERE `QID`='" . $_GET["QID"] . "'";
				$Erg = mysql_query($SQL1, $con);
				$SQL2 = "INSERT INTO `FAQ` Values ('', '" .
				mysql_result($Erg, 0, "Question") . "', '" . mysql_result($Erg, 0, "Answer") . "')";
				$Erg = db_query($SQL2, "trasfert to request to the FAQ");
				if ($Erg == 1)
					echo "\tDer Eintrag wurde &uuml;bertragen.<br />\n";
				else
					echo "\tEin Fehler ist aufgetreten. Sorry, du kannst es aber ja nochmal probieren :)\n";
			}

			break;

			// *---------------------------------------------------------------------------
			// * FAQ - Bearbeitung
			// *---------------------------------------------------------------------------
			// * je nach �bergabeoption ($quest) koennen FAQ's erfasst werden,
			// * geaendert oder geloscht werden...
			// *---------------------------------------------------------------------------
		case "faq" :
			$quest_bearb = 0; // keine Fragenliste anzeigen, FAQ editieren...
			echo "\tFAQ-Liste:<br />";
			echo "<a href=\"./faq.php?quest=faqnew\">Neuen Eintrag</a>";

			$SQL = "SELECT * FROM `FAQ`";
			$Erg = mysql_query($SQL, $con);

			// anzahl zeilen
			$Zeilen = mysql_num_rows($Erg);

			for ($n = 0; $n < $Zeilen; $n++)
				if (mysql_result($Erg, $n, "Antwort") != "") {
					echo "\t<p class=\"question\">" . mysql_result($Erg, $n, "Frage") . "</p>\n";
					echo "\t<p class=\"answetion\">" . mysql_result($Erg, $n, "Antwort") . "</p>\n";
					echo "\t<a href=\"./faq.php?quest=faqedit&FAQID=" . mysql_result($Erg, $n, "FID") .
					"\">Bearbeiten</a>\n<br />---<br />\n";
				}
			break;

		case "faqedit" :
			if (!IsSet ($_GET["FAQID"]))
				echo "\tFehlerhafter Aufruf...<br />Bitte die Bearbeitung nochmals beginnen :)\n";
			else {
				$SQL = "SELECT * FROM `FAQ` WHERE `FID`='" . $_GET["FAQID"] . "'";
				$Erg = mysql_query($SQL, $con);

				// anzahl zeilen
				$Zeilen = mysql_num_rows($Erg);
?>
  <form action="./faq.php" method="GET">
    Frage:<br />
  <textarea name="Frage" rows="3" cols="80"><?php echo mysql_result($Erg, 0, "Frage"); ?></textarea>
  <br /><br />
  Antwort:<br />
  <textarea name="Antwort" rows="5" cols="80"><?php echo mysql_result($Erg, 0, "Antwort"); ?></textarea><br />
  <input type="hidden" name="FAQID" value="<?php echo $_GET["FAQID"]; ?>">
  <input type="hidden" name="quest" value="faqsave">
  <input type="submit" value="Sichern...">
  </form>
  <form action="./faq.php">  
        <input type="hidden" name="FAQID" value="<?php echo $_GET["FAQID"]; ?>">
        <input type="hidden" name="quest" value="faqdelete">
        <input type="submit" value="L&ouml;schen...">
  </form>
<?php


			}
			break;

		case "faqdelete";
			if (!IsSet ($_GET["FAQID"]))
				echo "\tFehlerhafter Aufruf... Bitte die Bearbeitung nochmal starten...\n";
			else {
				$SQL = "DELETE FROM `FAQ` WHERE `FID`='" . $_GET["FAQID"] . "' LIMIT 1";
				$Erg = db_query($SQL, "delate faq item");
				if ($Erg == 1)
					echo "\tDer Eintrag wurde gel&ouml;scht<br />\n";
				else
					echo "\tEin Fehler ist aufgetreten. Ist der Eintag bereits gel&ouml;scht gewesen?\n";
			}
			break;

		case "faqsave";
			if (!IsSet ($_GET["FAQID"]))
				echo "\tFehlerhafter Aufruf... Bitte die Bearbeitung nochmal starten...\n";
			else {
				$SQL = "UPDATE `FAQ` SET `Frage`='" . $_GET["Frage"] . "', `Antwort`='" . $_GET["Antwort"] .
				"' WHERE `FID`='" . $_GET["FAQID"] . "' LIMIT 1";
				$Erg = db_query($SQL, $con);
				if ($Erg == 1)
					echo "\tDer Eintrag wurde ge&auml;ndert<br />\n";
				else
					echo "\tEin Fehler ist aufgetreten. Sorry, du kannst es aber ja nochmal probieren :)\n";
			}
			break;

		case "faqnew" :
?>
  <form action="./faq.php" method="GET">
        Frage:<br />
        <textarea name="Frage" rows="3" cols="80">Frage...</textarea><br /><br />
        Antwort:<br />
  <textarea name="Antwort" rows="5" cols="80">Antwort</textarea><br />
        <input type="hidden" name="quest" value="faqnewsave">
        <input type="submit" value="Sichern...">
        </form>
<?php


			break;

		case "faqnewsave";
			$SQL = "INSERT INTO `FAQ` VALUES ('', '" . $_GET["Frage"] . "', '" . $_GET["Antwort"] . "')";
			$Erg = db_query($SQL, "Save new FAQ entry");
			if ($Erg == 1)
				echo "\tDer Eintrag wurde erfasst.<br />\n";
			else
				echo "\tEin Fehler ist aufgetreten. Sorry, du kannst es aber ja nochmal probieren :)\n";
			break;

	} //switch ($_GET["quest"])

	// Hilfsroutine f�r die Anfragen:
	// Fragenliste anzeigen???
	if ($quest_bearb == 1) {
		$Erg = mysql_query($SQL, $con);
		// anzahl zeilen
		$Zeilen = mysql_num_rows($Erg);

		if ($Zeilen == 0)
			echo "\tkeine vorhanden...\n";
		else
			for ($n = 0; $n < $Zeilen; $n++) {
				echo "\t<p>" . nl2br(mysql_result($Erg, $n, "Question")) . "\n</p><br />\n";
				echo "\t<a href=\"./faq.php?quest=edit&QID=" . mysql_result($Erg, $n, "QID") . "\">Bearbeiten</a>\n";
				echo "<br />---<br />\n";
			}

	}

} //if (IsSet($_GET["quest"]))
else {
	echo "Bitte w&auml;hle aus, ob du:\n";
	echo "<ul>\n";
	echo "\t<li><a href=\"./faq.php?quest=all\">Alle Anfragen anzeigen/bearbeiten m&ouml;chtest</a></li>\n";
	echo "\t<li><a href=\"./faq.php?quest=open\">Alle offenen Anfragen anzeigen/bearbeiten m&ouml;chtest (" .
	noAnswer() . ")</a></li>\n";
	echo "\t<li><a href=\"./faq.php?quest=faq\">Die FAQ's anzeigen/bearbeiten</a></li>\n";
	echo "</ul>\n";
}

include ("includes/footer.php");
?>
