<?php
$title = "Index";
$header = "Index";
include ("../../27c3/includes/header.php");

echo Get_Text("index_text1")."<br><br>";
echo Get_Text("index_text2")."<br>";
echo Get_Text("index_text3")."<br>";

include ("../../27c3/includes/login_eingabefeld.php");

echo "<h6>".Get_Text("index_text4")."</h6>";

//echo Get_Text("index_text5"). "<br>". $show_SSLCERT;

include ("../../27c3/includes/footer.php");
?>


