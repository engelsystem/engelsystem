<?php

// Enable maintenance mode (showin a static page)
$maintenance_mode = (strtolower(getenv('MAINTENANCE_MODE')) === 'true');

// URL to the angel faq and job description
$faq_url = getenv('FAQ_URL');

// contact email address, linked on every page
$contact_email = getenv('CONTACT_EMAIL');

// Default-Theme auf der Startseite, 1=style1.css usw.
$default_theme = getenv('DEFAULT_THEME');

// Anzahl der News, die auf einer Seite ausgeben werden koennen...
$DISPLAY_NEWS = getenv('DISPLAY_NEWS');

// Anzahl Stunden bis zum Austragen eigener Schichten
$LETZTES_AUSTRAGEN = getenv('LETZTES_AUSTRAGEN');

// Setzt den zu verwendenden Crypto-Algorismus (entsprechend der Dokumentation von crypt()).
// Falls ein Benutzerpasswort in einem anderen Format gespeichert ist,
// wird es bei der ersten Benutzung des Klartext-Passworts in das neue Format
// konvertiert.
// define('CRYPT_ALG', '$1'); // MD5
// define('CRYPT_ALG', '$2y$13'); // Blowfish
// define('CRYPT_ALG', '$5$rounds=5000'); // SHA-256
define('CRYPT_ALG', '$6$rounds=5000'); // SHA-512

define('MIN_PASSWORD_LENGTH', 8);

// Wenn Engel beim Registrieren oder in ihrem Profil eine T-Shirt Größe angeben sollen, auf true setzen:
$enable_tshirt_size = (strtolower(getenv('ENABLE_TSHIRT_SIZE')) === 'true');

// Number of shifts to freeload until angel is locked for shift signup.
$max_freeloadable_shifts = getenv('MAX_FREELOADABLE_SHIFTS');

// local timezone
date_default_timezone_set(getenv('DATE_DEFAULT_TIMEZONE_SET'));

// multiply "night shifts" and freeloaded shifts (start or end between 2 and 6 exclusive) by 2
$shift_sum_formula = "SUM(
  (1+(
    (HOUR(FROM_UNIXTIME(`Shifts`.`end`)) > 2 AND HOUR(FROM_UNIXTIME(`Shifts`.`end`)) < 6)
    OR (HOUR(FROM_UNIXTIME(`Shifts`.`start`)) > 2 AND HOUR(FROM_UNIXTIME(`Shifts`.`start`)) < 6)
    OR (HOUR(FROM_UNIXTIME(`Shifts`.`start`)) <= 2 AND HOUR(FROM_UNIXTIME(`Shifts`.`end`)) >= 6)
  ))*(`Shifts`.`end` - `Shifts`.`start`)*(1 - 3 * `ShiftEntry`.`freeloaded`)
)";

// voucher calculation
$voucher_settings = array(
	"initial_vouchers" => 2,
	"shifts_per_voucher" => 1
);

// weigh every shift the same
// $shift_sum_formula = "SUM(`end` - `start`)";

// For accessing stats
$api_key = getenv('API_KEY');

// MySQL-Connection Settings
$config = array(
    'host' => getenv('MYSQL_HOSTNAME'),
    'user' => getenv('MYSQL_USERNAME'),
    'pw' => getenv('MYSQL_PASSWORD'),
    'db' => getenv('MYSQL_DB_NAME')
);
?>
