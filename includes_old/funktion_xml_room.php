<?php
  echo "<h1>Rooms:</h1>\n";

  function saveRoomData() {
    global $con;

    if(isset($_GET["NameXML"])) {
      $SQL = "INSERT INTO `Room` ( `Name`, `FromPentabarf` ) ".
        "VALUES ('". mysql_escape_string($_GET["NameXML"]). "', 'Y');";
      $Erg = mysql_query($SQL, $con);

      if($Erg)
        echo "Aenderung, an Raum ". $_GET["NameXML"]. ", war erfogreich<br />";
      else
        echo "Aenderung, an Raum ". $_GET["NameXML"]. ", war <u>nicht</u> erfogreich.(".

      mysql_error($con). ")<br />[$SQL]<br />";
    } else 
      echo "Fehler in den Parametern!<br />";
  }

  if(isset($_GET["RoomUpdate"]))
    saveRoomData();

  // INIT Status counter
  $DS_KO = 0;

  // Ausgabe
  echo "<table border=\"0\">\n";
  echo "<tr><th>Name</th><th>state</th></tr>\n";

  if($EnableSchudle) {
    foreach($XMLmain->sub as $EventKey => $Event) {
      if( $Event->name == "VEVENT") {
        $NameXML = getXMLsubData( $Event, "LOCATION");

        if( !isset( $RoomName[$NameXML])) {
          $RoomName[$NameXML] = "";

          if(isset($_GET["UpdateALL"])) {
            $_GET["NameXML"] = $NameXML;
            saveRoomData();
            CreateRoomArrays();
          } else {
            echo "<form action=\"dbUpdateFromXLS.php\">\n";
            echo "<tr>\n";
            echo "<td><input name=\"NameXML\" type=\"text\" value=\"$NameXML\" readonly></td>\n";
            echo "<td><input type=\"submit\" name=\"RoomUpdate\" value=\"update\"></td>\n";
            $DS_KO++;
            echo "</tr>\n";
            echo "</form>\n";
            echo "<br />";
          }
        }
      }
    }
  }

  echo "<tr><td colspan=\"6\">status: $DS_KO nicht vorhanden.</td></tr>\n";
  echo "</table>\n";
?>
