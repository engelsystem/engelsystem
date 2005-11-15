<?
// Adresse des Webservers
$url = "https://linuxeurobook/";

// Startverzeichnis des Engelhome
$ENGEL_ROOT = "/engel/";

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

//the link to the CCC page
//$CCC_Start = "https://pentabarf.cccv.de/pentabarf/event/";
$CCC_Start = "http://www.ccc.de/congress/2004/fahrplan/event/";
$CCC_End   = ".de.html";

// SSL Cert-KEY
$show_SSLCERT = "MD5:<br>AF:32:B9:BE:3F:AE:53:78:1E:1B:6E:82:48:E0:DB:94<br>SHA1:<br>B8:07:E8:A4:F3:1A:EF:03:81:C2:44:0C:50:25:3D:1A:A0:E4:AA:76";

//globale const. fuer schischtplan
$GlobalZeileProStunde = 4;

//ist ein modem angeschlossen
$ModemEnable = false;

//soll das xcal-file von penterbarf 
//$DataGetMeth="wget";
$DataGetMeth="lynx";

?>
