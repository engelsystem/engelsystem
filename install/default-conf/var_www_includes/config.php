<?php
// Adresse des Webservers
$url = "https://SEDENGELURL";

// Startverzeichnis des Engelhome
$ENGEL_ROOT = "/";

// Default-Theme auf der Startseite, 1=style1.css usw.
$default_theme = 10;

// System disable message, ist ist set is: bages schow only this text
//$SystemDisableMessage="<H1>This system ist moved to a server in the BCC, you can in the moment only youse it in the in Engel Room</H1>";

// Anzahl der News, die auf einer Seite ausgeben werden koennen...
$DISPLAY_NEWS = 6;

// Anzahl Stunden bis zum Austragen eigener Schichten
$LETZTES_AUSTRAGEN=3;

//Setzt den zu verwendenden Crypto algorismis 
// mp5 oder crypt
// achtung crypt schaltet password �ndern ab
$crypt_system="md5";
//$crypt_system="crypt";

// timezonen offsett
$gmdateOffset=3600;

// f�r Developen 1, sonst = 0
$debug = 0;

// SSL Cert-KEY
$show_SSLCERT = "MD5:<br>MD5SED<br>\n".
    "SHA1:<br>SHA1SED";

//globale const. fuer schischtplan
$GlobalZeileProStunde = 4;

//Tempdir
$Tempdir="./tmp";

// local timezone
date_default_timezone_set("Europe/Berlin");

//Pentabarf ConferenzDI f�r UpdateDB
$PentabarfXMLhost = "cccv.pentabarf.org";
$PentabarfXMLpath = "Xcal/conference/";
$PentabarfXMLEventID = "31";
$PentabarfGetWith = "fsockopen";        // "fsockopen"/"fopen"/"wget"/"lynx"


//Mailing List: is is not defined, the option is not shown
//$SubscribeMailinglist = "*-subscribe@lists.*";

/// Passord for external Authorization, function only active if the var is defined
//$CurrentExternAuthPass = 23;

?>
