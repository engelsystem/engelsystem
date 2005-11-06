<?php
$title = "Index";
$header = "Index";
$Page["Public"] = "Y";
include ("./inc/header.php");

echo Get_Text("index_text1")."<br><br>";
echo Get_Text("index_text2")."<br>";
echo Get_Text("index_text3")."<br>";

include ("./inc/login_eingabefeld.php");

echo "<h6>".Get_Text("index_text4")."</h6>";

echo Get_Text("index_text5"). "<br>". $show_SSLCERT;

include ("./inc/footer.php");
?>


