<?php
  header("Content-Type: application/json");

  include "../../../camp2011/includes/config.php";
  include "../../../camp2011/includes/config_db.php";

  $User = $_POST['user'];
  $Pass = $_POST['pw'];
  $SourceOuth = $_POST['so'];

  if(isset($CurrentExternAuthPass) && $SourceOuth == $CurrentExternAuthPass) {
    $sql = "SELECT * FROM `User` WHERE `Nick`='" . $User . "'";
    $Erg = mysql_query($sql, $con);

    if(mysql_num_rows($Erg) == 1) {
      if(mysql_result($Erg, 0, "Passwort") == $Pass) {
        $UID = mysql_result($Erg, 0, "UID");

        // get CVS import Data
        $SQL = "SELECT * FROM `UserCVS` WHERE `UID`='" . $UID . "'";
        $Erg_CVS =  mysql_query($SQL, $con);
        $CVS = mysql_fetch_array($Erg_CVS);

        $msg = array('status' => 'success', 'rights' => $CVS);
        echo json_encode($msg);
      } else
        echo json_encode(array('status' => 'failed'));
    } else
      echo json_encode(array('status' => 'failed'));
  } else
    echo json_encode(array('status' => 'failed'));
?>
