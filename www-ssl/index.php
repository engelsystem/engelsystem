<?php
  $title = "Start";
  $header = "Start";
  include "../includes/header.php";

  echo "<p>" . Get_Text("index_text1") . "</p>\n";
  echo "<p>" . Get_Text("index_text2") . "</p>\n";
  echo "<p>" . Get_Text("index_text3") . "</p>\n";

  include "../includes/login_eingabefeld.php";

  echo "<h6>" . Get_Text("index_text4") . "</h6>";

  include "../includes/footer.php";
?>
