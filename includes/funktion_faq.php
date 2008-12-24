<?PHP

function noAnswer() {
  global $con;

  $SQL = "SELECT UID FROM Questions WHERE `AID`='0'";
  $Res=mysql_query($SQL, $con);

  return mysql_num_rows($Res);
}

?>
