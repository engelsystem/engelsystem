<?php
$title = "Himmel";
$header = "News";

// Die Session zerstoeren...
session_start();
session_destroy ();
// und eine neue erstellen, damit kein Erzengelmenü angezeigt wird (falls sich ein Erzengel abmeldet...)
session_start();

include ("./inc/header.php");

echo Get_Text("index_logout")."<br><br>";

include ("./inc/login_eingabefeld.php");

include ("./inc/footer.php");
?>
