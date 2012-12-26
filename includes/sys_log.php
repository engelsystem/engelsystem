<?php

/**
 * Write a log entry. This should be used to log user's activity.
 * @param $message
 */
function engelsystem_log($message) {
  global $user;

  if(isset($user)) {
    $nick = $user['Nick'];
  } else {
    $nick = "Guest";
  }

  LogEntry_create($nick, $message);
}

?>