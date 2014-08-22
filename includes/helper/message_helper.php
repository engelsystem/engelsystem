<?php

/**
 * Gibt zwischengespeicherte Fehlermeldungen zurück und löscht den Zwischenspeicher
 */
function msg() {
  if (! isset($_SESSION['msg']))
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
    return '<div class="alert alert-info">' . $msg . '</div>';
  } else {
    if (! isset($_SESSION['msg']))
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
    return '<div class="alert alert-danger">' . $msg . '</div>';
  } else {
    if (! isset($_SESSION['msg']))
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
    return '<div class="alert alert-success">' . $msg . '</div>';
  } else {
    if (! isset($_SESSION['msg']))
      $_SESSION['msg'] = "";
    $_SESSION['msg'] .= success($msg, true);
  }
}

?>