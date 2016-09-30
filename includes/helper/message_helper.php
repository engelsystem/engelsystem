<?php

/**
 * Gibt zwischengespeicherte Fehlermeldungen zurück und löscht den Zwischenspeicher
 */
function msg() {
  if (! isset($_SESSION['msg'])) {
    return "";
  }
  $msg = $_SESSION['msg'];
  $_SESSION['msg'] = "";
  return $msg;
}

/**
 * Rendert eine Information
 */
function info($msg, $immediatly = false) {
  return alert('info', $msg, $immediatly);
}

/**
 * Rendert eine Fehlermeldung
 */
function error($msg, $immediatly = false) {
  return alert('danger', $msg, $immediatly);
}

/**
 * Rendert eine Erfolgsmeldung
 */
function success($msg, $immediatly = false) {
  return alert('success', $msg, $immediatly);
}

/**
 * Renders an alert with given alert-* class.
 */
function alert($class, $msg, $immediatly = false) {
  if ($immediatly) {
    if ($msg == "") {
      return "";
    }
    return '<div class="alert alert-' . $class . '">' . $msg . '</div>';
  }
  
  if (! isset($_SESSION['msg'])) {
    $_SESSION['msg'] = "";
  }
  $_SESSION['msg'] .= alert($class, $msg, true);
}

?>