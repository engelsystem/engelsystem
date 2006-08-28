<?PHP
echo "<br>";

if( strpos( $_SERVER["REQUEST_URI"], "?") >0)
	$URL = $_SERVER["REQUEST_URI"]. "&SetLanguage=";
else
	$URL = $_SERVER["REQUEST_URI"]. "?SetLanguage=";

echo "<a href=\"". $URL. "DE\"><img src=\"./inc/flag/de.gif\" alt=\"DE\"></a> ";
echo "<a href=\"". $URL. "EN\"><img src=\"./inc/flag/en.gif\" alt=\"En\"></a> ";

?>
