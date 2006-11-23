<?php
include("./inc/funktion_db.php");

$con = mysql_connect("localhost", "user", "pass") or die ("connection failed");
$sel = mysql_select_db("tabel") or die (mysql_error());
?>
