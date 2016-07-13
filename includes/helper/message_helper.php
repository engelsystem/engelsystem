<?php

/**
 * Are cached error messages and clears the latch
 */
function msg() {
  if (! isset($_SESSION['msg']))
    return "";
  $msg = $_SESSION['msg'];
  $_SESSION['msg'] = "";
  return $msg;
}

/**
 * Renders information
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
 * Renders an error message
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
 * Renders a success message
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