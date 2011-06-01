<?php


// Die Session zerstoeren usw
require_once ('bootstrap.php');
include "config/config.php";

session_start();
session_destroy();
// und eine neue erstellen, damit kein Erzengelmenue angezeigt wird (falls sich ein Erzengel abmeldet...)
session_start();

header("HTTP/1.1 302 Moved Temporarily");
header("Location: " . $url . $ENGEL_ROOT);
?>
