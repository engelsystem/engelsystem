<?php
if (strpos($_SERVER["REQUEST_URI"], "?") > 0)
	$URL = $_SERVER["REQUEST_URI"] . "&SetLanguage=";
else
	$URL = $_SERVER["REQUEST_URI"] . "?SetLanguage=";

echo '<p><a class="sprache" href="' . $URL . 'DE"><img src="' . $ENGEL_ROOT . 'pic/flag/de.png" alt="DE" title="Deutsch"></a>';
echo '<a class="sprache" href="' . $URL . 'EN"><img src="' . $ENGEL_ROOT . 'pic/flag/en.png" alt="EN" title="English"></a></p>';
?>
