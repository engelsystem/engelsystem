<?php

/**
 * Write a log entry.
 * This should be used to log user's activity.
 *
 * @param
 *          $message
 */
function engelsystem_log($message) {
  global $user;

  if (isset($user)) {
    $nick = User_Nick_render($user);
  } else {
    $nick = "Guest";
  }
  LogEntry_create($nick, $message);
}

/**
 * Generates a PHP Stacktrace.
 */
function debug_string_backtrace() {
  ob_start();
  debug_print_backtrace();
  $trace = ob_get_contents();
  ob_end_clean();

  // Remove first item from backtrace as it's this function which
  // is redundant.
  $trace = preg_replace('/^#0\s+' . __FUNCTION__ . "[^\n]*\n/", '', $trace, 1);

  // Renumber backtrace items.
  // $trace = preg_replace('/^#(\d+)/me', '\'#\' . ($1 - 1)', $trace);

  return $trace;
}

?>