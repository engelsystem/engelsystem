<?php

$title = "User-Liste";
$header = "Editieren der Engelliste";
include ("../../../camp2011/includes/header.php");
include ("../../../camp2011/includes/funktion_db_list.php");

if (IsSet($_GET["enterUID"]))
{ 
  // UserID wurde mit uebergeben --> Aendern...

  echo "Hallo,<br />".
     "hier kannst du den Eintrag &auml;ndern. Unter dem Punkt 'Gekommen' ".
    "wird der Engel als anwesend markiert, ein Ja bei Aktiv bedeutet, ".
    "dass der Engel aktiv war und damit ein Anspruch auf ein T-Shirt hat. ".
    "Wenn T-Shirt ein 'Ja' enth&auml;lt, bedeutet dies, dass der Engel ".
    "bereits sein T-Shirt erhalten hat.<br /><br />\n";


  $SQL_CVS = "SELECT * FROM `UserCVS` WHERE `UID`='". $_GET["enterUID"]. "'";
  $Erg_CVS =  mysql_query($SQL_CVS, $con);
    
  if( mysql_num_rows($Erg_CVS) != 1) 
    echo "Sorry, der Engel (UID=". $_GET["enterUID"]. ") wurde in der Liste nicht gefunden.";
  else
  {
    // Rename if is an group
    if( $_GET["enterUID"] < 0 ) {
      $SQLname = "SELECT `Name` FROM `UserGroups` WHERE `UID`='". $_GET["enterUID"]. "'";
            $ErgName = mysql_query($SQLname, $con);
            echo mysql_error($con);

      echo "<form action=\"./userSaveSecure.php?action=changeGroupName\" method=\"POST\">\n";
      echo "<input type=\"hidden\" name=\"enterUID\" value=\"". $_GET["enterUID"]. "\">\n";
      echo "<input type=\"text\" name=\"GroupName\" value=\"". mysql_result($ErgName, 0, "Name"). "\">\n";
      echo "<input type=\"submit\" value=\"rename\">\n";
      echo "</form>";
    }

    echo "<form action=\"./userSaveSecure.php?action=change\" method=\"POST\">\n";
    echo "<table border=\"0\">\n";
    echo "<input type=\"hidden\" name=\"Type\" value=\"Secure\">\n";
    echo "  <tr><td><br /><u>Rights of \"". UID2Nick($_GET["enterUID"]). "\":</u></td></tr>\n";


    $CVS_Data = mysql_fetch_array($Erg_CVS);
    $CVS_Data_i = 1;
    foreach ($CVS_Data as $CVS_Data_Name => $CVS_Data_Value) 
    {
        $CVS_Data_i++;
      //nur jeder zweiter sonst wird für jeden text noch die position (Zahl) ausgegeben
      if( $CVS_Data_i%2 && $CVS_Data_Name!="UID") 
      {
        if($CVS_Data_Name=="GroupID") {
          if( $_GET["enterUID"] > 0 )
          {
            echo "<tr><td><b>Group</b></td>\n".
              "<td><select name=\"GroupID\">";

            $SQL_Group = "SELECT * FROM `UserGroups`";
            $Erg_Group =  mysql_query($SQL_Group, $con);
            for ($n = 0 ; $n < mysql_num_rows($Erg_Group) ; $n++)
            {
              $UID =  mysql_result($Erg_Group, $n, "UID");
              echo "\t<option value=\"$UID\"";
              if( $CVS_Data_Value == $UID)
                echo " selected";
              echo ">". mysql_result($Erg_Group, $n, "Name"). "</option>\n";
            }
            echo "</select></td></tr>";
          }
        } else {
          echo "<tr><td>$CVS_Data_Name</td>\n<td>";
          echo "<input type=\"radio\" name=\"".($CVS_Data_i-1)."\" value=\"Y\" ";
          if( $CVS_Data_Value == "Y" )  
               echo " checked";
          echo ">allow \n";
          echo "<input type=\"radio\" name=\"".($CVS_Data_i-1)."\" value=\"N\" ";
          if( $CVS_Data_Value == "N" )
                echo " checked";
          echo ">denied \n";
          if( $_GET["enterUID"] > 0 )
          {
            echo "<input type=\"radio\" name=\"".($CVS_Data_i-1)."\" value=\"G\" ";
            if( $CVS_Data_Value == "G" )
                  echo " checked";
            echo ">group-setting \n";
            echo "</td></tr>";
              }
        }
      } //IF
    } //Foreach      
    echo "</td></tr>\n";
    
    // Ende Formular
    echo "</td></tr>\n";
    echo "</table>\n<br />\n";
    echo "<input type=\"hidden\" name=\"enterUID\" value=\"". $_GET["enterUID"]. "\">\n";
    echo "<input type=\"submit\" value=\"sichern...\">\n";
    echo "</form>";

    echo "<br /><form action=\"./userSaveSecure.php?action=delete\" method=\"POST\">\n";
    echo "<input type=\"hidden\" name=\"enterUID\" value=\"". $_GET["enterUID"]. "\">\n";
    echo "<input type=\"submit\" value=\"l&ouml;schen...\">\n";
    echo "</form>";
  } 
}

include ("../../../camp2011/includes/footer.php");
?>


