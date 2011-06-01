<?php
  $title = "Himmel";
  $header = "";

  include "../../../camp2011/includes/header.php";

  if(!isset($_GET["action"]))
    $_GET["action"] = "start";

  switch( $_GET["action"]) {
    case "start":
      echo Get_Text("Hello"). $_SESSION['Nick']. ", <br />\n";
      echo Get_Text("pub_messages_text1"). "<br /><br />\n";

      //show exist Messages
      $SQL = "SELECT * FROM `Messages` WHERE `SUID`='" . $_SESSION["UID"] . "' OR `RUID`='" . $_SESSION["UID"] . "'";
      $erg = mysql_query($SQL, $con);

      echo "<table border=\"0\" class=\"border\" cellpadding=\"2\" cellspacing=\"1\">\n";
      echo "<tr>\n";
      echo "<td class=\"contenttopic\"><b>". Get_Text("pub_messages_Datum"). "</b></td>\n";
      echo "<td class=\"contenttopic\"><b>". Get_Text("pub_messages_Von"). "</b></td>\n";
      echo "<td class=\"contenttopic\"><b>". Get_Text("pub_messages_An"). "</b></td>\n";
      echo "<td class=\"contenttopic\"><b>". Get_Text("pub_messages_Text"). "</b></td>\n";
      echo "<td class=\"contenttopic\"></td>\n";
      echo "</tr>\n";

      for($i = 0; $i < mysql_num_rows($erg); $i++) {
        echo "<tr class=\"content\">\n";
        echo "<td>" . mysql_result($erg, $i, "Datum") . "</td>\n";
        echo "<td>" . UID2Nick(mysql_result($erg, $i, "SUID")) . "</td>\n";
        echo "<td>" . UID2Nick(mysql_result($erg, $i, "RUID")) . "</td>\n";
        echo "<td>" . mysql_result($erg, $i, "Text") . "</td>\n";
        echo "<td>"; 

        if(mysql_result($erg, $i, "RUID") == $_SESSION["UID"]) {
          echo "<a href=\"?action=DelMsg&Datum=" . mysql_result($erg, $i, "Datum") . "\">" . Get_Text("pub_messages_DelMsg") . "</a>";

          if(mysql_result($erg, $i, "isRead") == "N")
            echo "<a href=\"?action=MarkRead&Datum=" . mysql_result($erg, $i, "Datum") . "\">" . Get_Text("pub_messages_MarkRead") . "</a>";
        } else {
          if(mysql_result($erg, $i, "isRead") == "N")
            echo Get_Text("pub_messages_NotRead");
        }

        echo "</td>\n";
        echo "</tr>\n";
    }

      // send Messeges
      echo "<form action=\"" . $_SERVER['SCRIPT_NAME'] . "?action=SendMsg\" method=\"POST\">";
      echo "<tr class=\"content\">\n";
      echo "<td></td>\n";
      echo "<td></td>\n";

      // Listet alle Nicks auf
      echo "<td><select name=\"RUID\">\n";

      $usql="SELECT * FROM `User` WHERE (`UID`!='". $_SESSION["UID"] ."') ORDER BY `Nick`";
      $uErg = mysql_query($usql, $con);
      $urowcount = mysql_num_rows($uErg);

      for ($k = 0; $k < $urowcount; $k++) {
        echo "<option value=\"" . mysql_result($uErg, $k, "UID") . "\">" . mysql_result($uErg, $k, "Nick") . "</option>\n";
      }

      echo "</select></td>\n";
      echo "<td><textarea name=\"Text\"  cols=\"30\" rows=\"10\"></textarea></td>\n";
      echo "<td><input type=\"submit\" value=\"" . Get_Text("save") . "\"></td>\n";
      echo "</tr>\n";
      echo "</form>";

      echo "</table>\n";
      break;

    case "SendMsg":
      echo Get_Text("pub_messages_Send1") . "...<br />\n";

    $SQL = "INSERT INTO `Messages` ( `Datum` , `SUID` , `RUID` , `Text` ) VALUES (".
           "'" . gmdate("Y-m-j H:i:s", time()) . "', ".
           "'" . $_SESSION["UID"]. "', ".
           "'" . $_POST["RUID"]."', ".
           "'" . $_POST["Text"]. "');";

    $Erg = mysql_query($SQL, $con);

    if($Erg == 1) 
      echo Get_Text("pub_messages_Send_OK") . "\n";
    else 
      echo Get_Text("pub_messages_Send_Error") . "...\n(". mysql_error($con). ")";
    break;

    case "MarkRead":
      $SQL = "UPDATE `Messages` SET `isRead` = 'Y' ".
             "WHERE `Datum` = '". $_GET["Datum"]. "' AND `RUID`='". $_SESSION["UID"]. "' ".
             "LIMIT 1 ;";
      $Erg = mysql_query($SQL, $con);

      if ($Erg == 1) 
        echo Get_Text("pub_messages_MarkRead_OK"). "\n";
      else 
        echo Get_Text("pub_messages_MarkRead_KO"). "...\n(". mysql_error($con). ")";
    break;

    case "DelMsg":
      $SQL = "DELETE FROM `Messages` ".
             "WHERE `Datum` = '". $_GET["Datum"]. "' AND `RUID` ='". $_SESSION["UID"]. "' ".
             "LIMIT 1;";
      $Erg = mysql_query($SQL, $con);

      if ($Erg == 1) 
        echo Get_Text("pub_messages_DelMsg_OK"). "\n";
      else 
        echo Get_Text("pub_messages_DelMsg_KO"). "...\n(". mysql_error($con). ")";
    break;

    default:
      echo Get_Text("pub_messages_NoCommand");
  }

  include "../../../camp2011/includes/footer.php";
?>
