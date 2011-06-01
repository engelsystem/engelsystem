<?php
ini_set("session.gc_maxlifetime", "65535");

include "config/config.php";
include "error_handler.php";
include "config/config_db.php";
include "funktion_lang.php";
include "funktion_faq.php"; // fuer noAnswer() im menu
include "funktion_menu.php";
include "funktion_user.php";

if (isset ($SystemDisableMessage) && (strlen($SystemDisableMessage) > 0)) {
	echo "<html><head><title>" . $SystemDisableMessage . "</title></head>";
	echo "<body>" . $SystemDisableMessage . "</body></html>\n";
	die();
}

if (!isset ($_SESSION))
	session_start();

include "secure.php";

if (!isset ($_SESSION['IP']))
	$_SESSION['IP'] = $_SERVER['REMOTE_ADDR'];

if (isset ($_SESSION['UID']) && ($_SESSION['IP'] <> $_SERVER['REMOTE_ADDR'])) {
	session_destroy();
	header("Location: " . $url . $ENGEL_ROOT);
}

include "UserCVS.php";

// update LASTlogin
if (isset ($_SESSION['UID'])) {
	$SQLlastLogIn = "UPDATE `User` SET " .
	"`lastLogIn` = '" . time() . "'" .
	" WHERE `UID` = '" . $_SESSION['UID'] . "' LIMIT 1;";
	mysql_query($SQLlastLogIn, $con);
}
?>
