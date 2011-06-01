<?php
  $SQL = "SELECT `Anz` FROM `Counter` WHERE `URL`=\"" . $Page["Name"] . "\"";
  $Erg = mysql_query($SQL, $con);

  echo mysql_error($con);

  if(mysql_num_rows($Erg) == 0) {
    $SQL = "INSERT INTO `Counter` ( `URL` , `Anz` ) VALUES ('" . $Page["Name"] . "', '1');";
    $Erg = mysql_query($SQL, $con);
  } elseif(mysql_num_rows($Erg) == 1) {
    $SQL = "UPDATE `Counter` SET `Anz` = '" . (mysql_result($Erg, 0, 0) +1) . "' WHERE `URL` = '" . $Page["Name"] . "' LIMIT 1 ;";
    $Erg = mysql_query($SQL, $con);
  }
?>
