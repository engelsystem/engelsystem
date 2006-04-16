<?PHP
// Adresse des Webservers
$url = "https://172.16.16.40/";

// Startverzeichnis des Engelhome
$ENGEL_ROOT = "/engel/";

// System disable message, ist ist set is: bages schow only this text
//$SystemDisableMessage="<H1>This system ist moved to a server in the BCC, you can in the moment only youse it in the in Engel Room</H1>";

// Anzahl der News, die auf einer Seite ausgeben werden koennen...
$DISPLAY_NEWS = 6;

// Anzahl Stunden bis zum Austragen eigener Schichten
$LETZTES_AUSTRAGEN=3;

//Setzt den zu verwendenden Crypto algorismis 
// mp5 oder crypt
// achtung crypt schaltet password ändern ab
$crypt_system="md5";
//$crypt_system="crypt";


// für Developen 1, sonst = 0
$DEBUG = 0;

// SSL Cert-KEY
$show_SSLCERT = "MD5:<br>AF:32:B9:BE:3F:AE:53:78:1E:1B:6E:82:48:E0:DB:94<br>\n".
		"SHA1:<br>B8:07:E8:A4:F3:1A:EF:03:81:C2:44:0C:50:25:3D:1A:A0:E4:AA:76";

//globale const. fuer schischtplan
$GlobalZeileProStunde = 4;

//ist ein modem angeschlossen
$ModemEnable = false;

//soll das xcal-file von penterbarf 
//$DataGetMeth="wget";
$DataGetMeth="lynx";

?>
