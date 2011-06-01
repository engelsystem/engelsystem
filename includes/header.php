<?php
include "header_start.php";

echo "<!DOCTYPE html>\n";
?>
<html> 
<head>

<title><?php echo $title; ?> - Engelsystem</title>
<meta charset="UTF-8" />
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<meta name="content-style-type" content="text/css" />
<meta name="keywords" content="Engel, Himmelsverwaltung" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="expires" content="0" />
<meta name="robots" content="index" />
<meta name="revisit-after" content="1 days" />
<script type="text/javascript" src="css/grossbild.js"></script>
<link rel="stylesheet" type="text/css" href="css/base.css" />
<link rel="stylesheet" type="text/css" href="css/style<?php echo isset($_SESSION['color']) ? $_SESSION['color'] : $default_theme ?>.css" />
<link rel="stylesheet" type="text/css" href="../css/base.css" />
<link rel="stylesheet" type="text/css" href="../css/style<?php echo isset($_SESSION['color']) ? $_SESSION['color'] : $default_theme ?>.css" />

<?php


if (isset ($reload)) {
	if ($reload == "")
		$reload = 3330;

	echo "\n<meta http-equiv=\"refresh\" content=\"" . $reload . "; URL=./?reload=" . $reload . "\">\n";
}

if (isset ($Page["AutoReload"]))
	echo "\n<meta http-equiv=\"refresh\" content=\"" . $Page["AutoReload"] .
	"; URL=" . $url . $ENGEL_ROOT . $Page["Name"] . "\">\n";

echo "</head>\n";

/////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////// B O D Y
/////////////////////////////////////////////////////////////////////////////////////////////
echo "<body>\n";

echo '<header><a href="' . $url . $ENGEL_ROOT . '" id="logo"></a></header>';

//ausgabe new message
if (isset ($_SESSION['CVS']["nonpublic/messages.php"])) {
	if ($_SESSION['CVS']["nonpublic/messages.php"] == "Y") {
		$SQL = "SELECT `Datum` FROM `Messages` WHERE `RUID`=" . $_SESSION["UID"] . " AND `isRead`='N'";
		$erg = mysql_query($SQL, $con);
		if (mysql_num_rows($erg) > 0)
			echo "<br /><a href=\"" . $url . $ENGEL_ROOT .
			"nonpublic/messages.php\">" . Get_Text("pub_messages_new1") .
			" " . mysql_num_rows($erg) . " " .
			Get_Text("pub_messages_new2") . "</a><br /><br />";
	}
}
?>
<table id="body" width="90%" align="center" border="0" cellpadding="7" cellspacing="0">
  <tr>
<?php


//ausgaeb Menu
if (!isset ($_SESSION['Menu']))
	$_SESSION['Menu'] = "L";
if ($_SESSION['Menu'] == "L")
	include ("menu.php");
?>

    <td valign="top" align="center">
<table border="0" width="100%" align="center" class="border" cellpadding="5" cellspacing="1">
  <tr class="contenttopic">
    <td>
<?php


echo "<a name=\"" . $header . "\" class=\"contenttopic\">";
if (strlen($header) == 0)
	echo "\n<b>" . Get_Text($Page["Name"]) . "</b></a>\n";
else
	echo "\n<b>$header</b></a>\n";
?>
    </td>
  </tr>
  <tr class="content">
     <td>
<br />
<?php


if (isset ($_SESSION['UID'])) {
	if (isset ($_SESSION['oldurl']))
		$BACKUP_SESSION_OLDURL = $_SESSION['oldurl'];
	if (isset ($_SESSION['newurl']))
		$_SESSION['oldurl'] = $_SESSION['newurl'];
	$_SESSION['newurl'] = $_SERVER["REQUEST_URI"];
}

function SetHeaderGo2Back() {
	global $BACKUP_SESSION_OLDURL;
	$_SESSION['oldurl'] = $BACKUP_SESSION_OLDURL;
}

if ($Page["CVS"] != "Y") {
	echo "Du besitzt kein Rechte f&uuml;r diesen Bereich.<br />\n";

	if (isset ($_SESSION['oldurl']))
		echo "<a href=\"" . $_SESSION["oldurl"] . "\">" . Get_Text("back") . "</a> geht's zur&uuml;ck...\n";
	else
		echo "<a href=\"" . $url . $ENGEL_ROOT . "\">" . Get_Text("back") . "</a> geht's zur&uuml;ck...\n";

	exit ();
}
?>

<!-- ende des header parts //-->
