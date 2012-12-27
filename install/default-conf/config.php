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

// Setzt den zu verwendenden Crypto-Algorismus (entsprechend der Dokumentation von crypt()).
// Falls ein Benutzerpasswort in einem anderen Format gespeichert ist,
// wird es bei der ersten Benutzung des Klartext-Passworts in das neue Format
// konvertiert.
//define('CRYPT_ALG', '$1'); // MD5
//define('CRYPT_ALG', '$2y$13'); // Blowfish
//define('CRYPT_ALG', '$5$rounds=5000'); // SHA-256
define('CRYPT_ALG', '$6$rounds=5000'); // SHA-512

define('MIN_PASSWORD_LENGTH', 8);

// Wenn Engel beim Registrieren oder in ihrem Profil eine T-Shirt Größe angeben sollen, auf true setzen:
$enable_tshirt_size = false;

// timezonen offsett
$gmdateOffset=3600;

// für Developen 1, sonst = 0
$debug = 0;

//globale const. fuer schischtplan
$GlobalZeileProStunde = 4;

//Tempdir
$Tempdir="./tmp";

// local timezone
date_default_timezone_set("Europe/Berlin");

//Pentabarf ConferenzDI für UpdateDB
$PentabarfXMLhost = "cccv.pentabarf.org";
$PentabarfXMLpath = "Xcal/conference/";
$PentabarfXMLEventID = "31";

//Mailing List: is is not defined, the option is not shown
//$SubscribeMailinglist = "*-subscribe@lists.*";

/// Passord for external Authorization, function only active if the var is defined
//$CurrentExternAuthPass = 23;

// multiply "night shifts" (start or end between 2 and 6 exclusive) by 2
$shift_sum_formula = "SUM(
  (1+(
    (HOUR(FROM_UNIXTIME(`Shifts`.`end`)) > 2 AND HOUR(FROM_UNIXTIME(`Shifts`.`end`)) < 6)
    OR (HOUR(FROM_UNIXTIME(`Shifts`.`start`)) > 2 AND HOUR(FROM_UNIXTIME(`Shifts`.`start`)) < 6)
    OR (HOUR(FROM_UNIXTIME(`Shifts`.`start`)) <= 2 AND HOUR(FROM_UNIXTIME(`Shifts`.`end`)) >= 6)
  ))*(`Shifts`.`end` - `Shifts`.`start`)
)";

// weigh every shift the same
//$shift_sum_formula = "SUM(`end` - `start`)";
?>
