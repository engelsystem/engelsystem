<?php
  $title = "Himmel";
  $header = "Engelbesprechung";
  include "../../../camp2011/includes/header.php";

  $SQL = "SELECT * FROM `News` ORDER BY 'Datum' DESC";
  $Erg = mysql_query($SQL, $con);

  // anzahl zeilen
  $Zeilen  = mysql_num_rows($Erg);

  for ($n = 0 ; $n < $Zeilen ; $n++) {
    if (mysql_result($Erg, $n, "Treffen") == "1") {
      echo "<p class='question'><u>" . mysql_result($Erg, $n, "Betreff") . "</u>";

      // Show Admin Page
      if($_SESSION['CVS']["admin/news.php"] == "Y")
        echo " <a href=\"./../admin/news.php?action=change&date=". mysql_result($Erg, $n, "Datum"). "\">[edit]</a>";

      echo "<br />&nbsp; &nbsp;<font size=1>".mysql_result($Erg, $n, "Datum").", ";
      echo UID2Nick(mysql_result($Erg, $n, "UID"))."</font></p>\n";
      echo "<p class='answetion'>".nl2br(mysql_result($Erg, $n, "Text"))."</p>\n";
    }
  }

  include ("../../../camp2011/includes/footer.php");
?>
