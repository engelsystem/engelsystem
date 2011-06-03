<?php

// Bleibt erstmal, damit Benutzer, die die Schnittstelle nutzen mitkriegen, dass diese Umgezogen ist
echo json_encode(array (
	'status' => 'failed',
	'error' => "JSON Service moved to https://engelsystem.de/?auth&user=<user>&pw=<password>&so=<key>"
));
?>
