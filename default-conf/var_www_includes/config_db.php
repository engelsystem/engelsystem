<?php
include("../include/funktion_db.php");

$con = mysql_connect("localhost", "root", "changeme") or die ("connection failed");
$sel = mysql_select_db("tabel") or die (mysql_error());
?>
