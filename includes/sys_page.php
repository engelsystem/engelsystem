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
 * Testet, ob der angegebene REQUEST Wert ein Integer ist, bzw.
 * eine ID sein könnte.
 */
function test_request_int($name) {
  if (isset($_REQUEST[$name]))
    return preg_match("/^[0-9]*$/", $_REQUEST[$name]);
  return false;
}

/**
 * Gibt den gefilterten REQUEST Wert mit Zeilenumbrüchen zurück
 */
function strip_request_item_nl($name) {
  return preg_replace("/([^\p{L}\p{S}\p{P}\p{Z}\p{N}+\n]{1,})/ui", '', strip_tags($_REQUEST[$name]));
}

/**
 * Entfernt unerwünschte Zeichen
 */
function strip_item($item) {
  return preg_replace("/([^\p{L}\p{S}\p{P}\p{Z}\p{N}+]{1,})/ui", '', strip_tags($item));
}

/**
 * Überprüft eine E-Mail-Adresse.
 */
function check_email($email) {
  return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

?>
