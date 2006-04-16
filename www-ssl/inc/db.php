<?php
include("./inc/funktion_db.php");

$con = mysql_connect("localhost", "engel", "engel") or die ("connection failed");
$sel = mysql_select_db("Himmel") or die (mysql_error());
?>
