<?php
  if($debug)
    echo "secure.php START<br />\n";

  foreach ($_GET as $k => $v) {
    $v = htmlentities($v, ENT_QUOTES);
    preg_replace('/([\'"`\'])/', '', $v);
    $_GET[$k] = $v;

    if($debug)
      echo "GET $k=\"$v\"<br />";
  }
  
  foreach ($_POST as $k => $v) {
    $v = htmlentities($v, ENT_QUOTES);
    preg_replace('/([\'"`\'])/', '', $v);
    $_POST[$k] = $v;
  
    if($debug)
      echo "POST $k=\"$v\"<br />";
  }

  if($debug)
    echo "secure.php END<br />\n";
?>
