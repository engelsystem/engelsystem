<?php
$title = "Himmel";
$header = "FAQ / Fragen an die Erzengel";
$submenus = 1;
include ("./inc/header.php");
include ("./inc/funktion_user.php");

//var init
$quest_bearb=0;

if (IsSet($_GET["quest"])) {

switch ($_GET["quest"]) 
{

// *---------------------------------------------------------------------------
// * Anfragen - Bearbeitung
// *---------------------------------------------------------------------------
// * je nach Übergabeoption ($quest) koennen Anfragen beantwortet werden oder
// * als FAQ uebergeben werden 
// *---------------------------------------------------------------------------

case "all":
	$SQL="Select * from Questions ORDER BY QID DESC";
?>
	Alle Anfragen:<br>
        <table  width="100%" class="border" cellpadding="2" cellspacing="1">
                <tr class="contenttopic">
			<th>Frage</th>
			<th>Anfragender</th>
                        <th>Beantwortet?</th>
                        <th>Antwort</th>
                        <th>Antwort von</th>
			<th>change</th>
                </tr>

<?

        $Erg = mysql_query($SQL, $con);
        // anzahl zeilen
        $Zeilen  = mysql_num_rows($Erg);
        for ($n = 0 ; $n < $Zeilen ; $n++) {
		echo "<tr class=\"content\">\n";
		echo "<td>".mysql_result($Erg, $n, "Question")."</td>\n";
		echo "<td>".UID2Nick(mysql_result($Erg, $n, "UID"))."</td>\n";
		echo "<td>";
		if (mysql_result($Erg, $n, "AID")>0) { 
			echo "Ja</td>\n";
			echo "<td>".mysql_result($Erg, $n, "Answer")."</td>\n";
			echo "<td>".UID2Nick(mysql_result($Erg, $n, "AID"))."</td>\n";
		} else {
			echo "Nein</td>\n";
			echo "<td>&nbsp;</td>\n";
			echo "<td>&nbsp;</td>\n";
		}
				echo "<td><a href=\"faq.php?quest=edit&QID=".mysql_result($Erg, $n, "QID")."\">xxx</a></td>";
		echo "</tr>\n";
	}
	echo "</table>\n";
	break;
case "open":
	$SQL="Select * from Questions where AID = \"0\" ORDER BY QID DESC";
	$quest_bearb=1; // Fragenliste anzeigen
?>
		Offene Anfragen:<br>
<?php
	break;
case "edit":
	$quest_bearb=0; // keine Fragenliste anzeigen, Frage editieren...
	if (!IsSet($_GET["QID"])){
?>
		Fehlerhafter Aufruf...<br>Bitte die Bearbeitung nochmals beginnen :)
<?php
	} else {
	$SQL = "SELECT * FROM Questions where QID=". $_GET["QID"];
	$Erg = mysql_query($SQL, $con);
?>
	<form action="./faq.php" method="GET">
		Anfrage von <b><?php echo UID2NICK(mysql_result($Erg, 0, "UID")); ?></b>:<br>
		<textarea name="Question" rows="3" cols="80"><?php echo mysql_result($Erg, 0, "Question"); ?></textarea>
		<br><br>
		Antwort der Erzengel:<br>
<?php
	if (mysql_result($Erg, 0, "Answer")=="") {
?>
		<textarea name="Answer" rows="5" cols="80">Bitte hier die Antwort eintragen...</textarea>
		<br>
<?php 
	} else {
?>
		<textarea name="Answer" rows="5" cols="80"><?php echo mysql_result($Erg, 0, "Answer"); ?></textarea>
		<br>
<?php
	}
?>
		<input type="hidden" name="QID" value="<? echo $_GET["QID"]; ?>">
		<input type="hidden" name="quest" value="save">
		<input type="submit" value="Sichern...">
	</form>
	Wenn diese Anfrage bereits beantwortet wurde, kannst du diese so wie sie ist als Engel-FAQ eintrag &uuml;bernehmen.<br>
	In diesem Falle erscheint hier der Link: 
<?php
	if (mysql_result($Erg, 0, "AID")<>"0") {
?>
	<a href="./faq.php?quest=transfer&QID=<?php echo $QID; ?>">Als FAQ-Eintrag sichern...</a>
<?php
	}
	
	} // Abfrage der QID
	break;

case "save":
	if (!IsSet($_GET["QID"])){
?>
	  Fehlerhafter Aufruf... Bitte die Bearbeitung nochmal starten...
<?php
        } else {
	  $SQL = "UPDATE Questions SET Question=\"". $_GET["Question"]. 
	  	 "\", AID=\"". $_SESSION['UID']. "\" , Answer=\"". $_GET["Answer"]. "\" ".
		 "where QID = \"". $_GET["QID"]. "\" LIMIT 1";
          $Erg = mysql_query($SQL, $con);
          if ($Erg == 1) {
?>
		Der Eintrag wurde ge&auml;ndert<br>
<?php
          } else {
?>
		Ein Fehler ist aufgetreten. Sorry, du kannst es aber ja nochmal probieren :)
<?php
	  }
        }
	break;

case "transfer":
	if (!IsSet($_GET["QID"])){
?>
	  Fehlerhafter Aufruf... Bitte die Bearbeitung nochmal starten...
<?php
        } else {
	
		$SQL1="Select * from Questions where QID=". $_GET["QID"];
		$Erg = mysql_query($SQL1, $con);
		$SQL2="Insert into FAQ Values (\"\", \"".
			mysql_result($Erg, 0, "Question")."\", \"".mysql_result($Erg, 0, "Answer")."\")";
		$Erg = mysql_query($SQL2, $con);
		if ($Erg == 1) {
?>
			Der Eintrag wurde &uuml;bertragen.<br>
<?php
	        } else {
?>
			Ein Fehler ist aufgetreten. Sorry, du kannst es aber ja nochmal probieren :)
<?php
	        }
	}
	
	break;

// *---------------------------------------------------------------------------
// * FAQ - Bearbeitung
// *---------------------------------------------------------------------------
// * je nach Übergabeoption ($quest) koennen FAQ's erfasst werden,
// * geaendert oder geloscht werden...
// *---------------------------------------------------------------------------


case "faq":
	$quest_bearb=0; // keine Fragenliste anzeigen, FAQ editieren...
?>
	FAQ-Liste:<br>
	<a href="./faq.php?quest=faqnew">Neuen Eintrag</a>
<?php
	$SQL = "SELECT * FROM `FAQ`";
	$Erg = mysql_query($SQL, $con);

	// anzahl zeilen
	$Zeilen  = mysql_num_rows($Erg);

	for ($n = 0 ; $n < $Zeilen ; $n++) {
	  if (mysql_result($Erg, $n, "Antwort")!="") {
?>
	      <p class='question'><?php echo mysql_result($Erg, $n, "Frage"); ?></p>
	      <p class='answetion'><?php echo mysql_result($Erg, $n, "Antwort"); ?></p>
              <a href="./faq.php?quest=faqedit&FAQID=<?php echo mysql_result($Erg, $n, "FID"); ?>">Bearbeiten</a>
              <br>---<br>
<?php 
}
	}
	break;

case "faqedit":
       if (!IsSet($_GET["FAQID"]))
       {
?>
	 Fehlerhafter Aufruf...<br>Bitte die Bearbeitung nochmals beginnen :)
<?php
	} else {

	$SQL = "SELECT * FROM FAQ where FID=". $_GET["FAQID"];
	$Erg = mysql_query($SQL, $con);

	// anzahl zeilen
	$Zeilen  = mysql_num_rows($Erg);
?>
	<form action="./faq.php" method="GET">
		Frage:<br>
	<textarea name="Frage" rows="3" cols="80"><?php echo mysql_result($Erg, 0, "Frage"); ?></textarea>
	<br><br>
	Antwort:<br>
	<textarea name="Antwort" rows="5" cols="80"><?php echo mysql_result($Erg, 0, "Antwort"); ?></textarea><br>
	<input type="hidden" name="FAQID" value="<? echo $_GET["FAQID"]; ?>">
	<input type="hidden" name="quest" value="faqsave">
	<input type="submit" value="Sichern...">
	</form>
	<form action="./faq.php">	
        <input type="hidden" name="FAQID" value="<? echo $_GET["FAQID"]; ?>">
        <input type="hidden" name="quest" value="faqdelete">
        <input type="submit" value="L&ouml;schen...">
	</form>
<?php 
	}
	break;

case "faqdelete";
	if (!IsSet($_GET["FAQID"]))
	{
?>
		Fehlerhafter Aufruf... Bitte die Bearbeitung nochmal starten...
<?php 
	} else {
		$SQL = "delete from FAQ where FID = \"". $_GET["FAQID"]. "\" LIMIT 1";
		$Erg = mysql_query($SQL, $con);
		if ($Erg == 1) {
?>
			Der Eintrag wurde gel&ouml;scht<br>
<?php
		} else {
?>
			Ein Fehler ist aufgetreten. Ist der Eintag bereits gel&ouml;scht gewesen?
<?php
		}
	}
	break;

case "faqsave";
        if (!IsSet($_GET["FAQID"]))
	{
?>
	  Fehlerhafter Aufruf... Bitte die Bearbeitung nochmal starten...
<?php
        } else {
          $SQL = "UPDATE FAQ SET Frage=\"". $_GET["Frage"]. "\", Antwort=\"". $_GET["Antwort"]. 
	  	 "\" where FID = \"". $_GET["FAQID"]. "\" LIMIT 1";
          $Erg = mysql_query($SQL, $con);
          if ($Erg == 1) {
?>
		Der Eintrag wurde ge&auml;ndert<br>
<?php
          } else {
?>
		Ein Fehler ist aufgetreten. Sorry, du kannst es aber ja nochmal probieren :)
<?php
	  }
        }
	break;

case "faqnew":
?>
	<form action="./faq.php" method="GET">
        Frage:<br>
        <textarea name="Frage" rows="3" cols="80">Frage...</textarea><br><br>
        Antwort:<br>
	<textarea name="Antwort" rows="5" cols="80">Antwort</textarea><br>
        <input type="hidden" name="quest" value="faqnewsave">
        <input type="submit" value="Sichern...">
        </form>
<?php
	break;
case "faqnewsave";
        $SQL = "INSERT INTO FAQ VALUES (\"\", \"". $_GET["Frage"]. "\", \"". $_GET["Antwort"]. "\")";
        $Erg = mysql_query($SQL, $con);
        if ($Erg == 1) {
?>
		Der Eintrag wurde erfasst.<br>
<?php
        } else {
?>
		Ein Fehler ist aufgetreten. Sorry, du kannst es aber ja nochmal probieren :)
<?php
	}
        break;

}

// Hilfsroutine für die Anfragen:
// Fragenliste anzeigen???

if ($quest_bearb==1) {

	$Erg = mysql_query($SQL, $con);
	// anzahl zeilen
	$Zeilen  = mysql_num_rows($Erg);

	if ($Zeilen==0){
?>
		keine vorhanden...
<?php 
	} else {
		for ($n = 0 ; $n < $Zeilen ; $n++) {
?>
<p>
<?php echo nl2br(mysql_result($Erg, $n, "Question"))."\n"; ?>
</p>
			<br>
			<a href="./faq.php?quest=edit&QID=<?php echo mysql_result($Erg, $n, "QID"); ?>">Bearbeiten</a>
			<br>---<br>
<?php
		}
	}

	
}

} else {

?>
Bitte w&auml;hle aus, ob du:

<ul>
	<li><a href="./faq.php?quest=all">Alle Anfragen anzeigen/bearbeiten m&ouml;chtest</a></li>
	<li><a href="./faq.php?quest=open">Alle offenen Anfragen anzeigen/bearbeiten m&ouml;chtest</a></li>
	<li><a href="./faq.php?quest=faq">Die FAQ's anzeigen/bearbeiten</a></li>
</ul>

<?php

}

include ("./inc/footer.php");
?>
