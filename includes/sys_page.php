<?php


/**
 * Leitet den Browser an die übergebene URL weiter und hält das Script an.
 */
function redirect($to) {
	header("Location: " . $to, true, 302);
	die();
}

/**
 * Gibt den gefilterten REQUEST Wert ohne Zeilenumbrüche zurück
 */
function strip_request_item($name) {
	return strip_item($_REQUEST[$name]);
}

/**
 * Testet, ob der angegebene REQUEST Wert ein Integer ist, bzw. eine ID sein könnte.
 */
function test_request_int($name) {
	if (isset ($_REQUEST[$name]))
		return preg_match("/^[0-9]*$/", $_REQUEST[$name]);
	return false;
}

/**
 * Gibt den gefilterten REQUEST Wert mit Zeilenumbrüchen zurück
 */
function strip_request_item_nl($name) {
	return preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}+\n]{1,})/ui", '', strip_tags($_REQUEST[$name]));
}

/**
 * Entfernt unerwünschte Zeichen
 */
function strip_item($item) {
	return preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}+]{1,})/ui", '', strip_tags($item));
}

/**
 * Gibt zwischengespeicherte Fehlermeldungen zurück und löscht den Zwischenspeicher
 */
function msg() {
	if (!isset ($_SESSION['msg']))
		return "";
	$msg = $_SESSION['msg'];
	$_SESSION['msg'] = "";
	return $msg;
}

/**
 * Rendert eine Information
 */
function info($msg, $immediatly = false) {
	if ($immediatly) {
		if ($msg == "")
			return "";
		return '<p class="info">' . $msg . '</p>';
	} else {
		if (!isset ($_SESSION['msg']))
			$_SESSION['msg'] = "";
		$_SESSION['msg'] .= info($msg, true);
	}
}

/**
 * Rendert eine Fehlermeldung
 */
function error($msg, $immediatly = false) {
	if ($immediatly) {
		if ($msg == "")
			return "";
		return '<p class="error">' . $msg . '</p>';
	} else {
		if (!isset ($_SESSION['msg']))
			$_SESSION['msg'] = "";
		$_SESSION['msg'] .= error($msg, true);
	}
}

/**
 * Rendert eine Erfolgsmeldung
 */
function success($msg, $immediatly = false) {
	if ($immediatly) {
		if ($msg == "")
			return "";
		return '<p class="success">' . $msg . '</p>';
	} else {
		if (!isset ($_SESSION['msg']))
			$_SESSION['msg'] = "";
		$_SESSION['msg'] .= success($msg, true);
	}
}
?>
