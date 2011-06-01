<?php
require_once ('../bootstrap.php');

$title = "News";
$header = "News";
include "includes/header.php";

echo "<a href=\"#Neu\">" . Get_Text(3) . "</a>";
include "news_output.php";

include "includes/footer.php";
?>
