<?php


/**
 * Liste verfügbarer Sprachen
 */
$languages = array (
	'DE' => "Deutsch",
	'EN' => "English"
);

function Get_Text($TextID, $NoError = false) {
	global $con, $error_messages, $debug;

	if (!isset ($_SESSION['Sprache']))
		$_SESSION['Sprache'] = "EN";
	if ($_SESSION['Sprache'] == "")
		$_SESSION['Sprache'] = "EN";
	if (isset ($_GET["SetLanguage"]))
		$_SESSION['Sprache'] = $_GET["SetLanguage"];

	$SQL = "SELECT * FROM `Sprache` WHERE TextID=\"$TextID\" AND Sprache ='" . $_SESSION['Sprache'] . "'";
	@ $Erg = mysql_query($SQL, $con);

	if (mysql_num_rows($Erg) == 1)
		return (@ mysql_result($Erg, 0, "Text"));
	elseif ($NoError && !$debug) return "";
	else {
		return "Error Data, '$TextID' found " . mysql_num_rows($Erg) . "x";
	}
}

function Print_Text($TextID, $NoError = false) {
	echo Get_Text($TextID, $NoError);
}
?>
