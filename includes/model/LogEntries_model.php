<?php

/**
 * Creates a log entry.
 * @param $nick Username
 * @param $message Log Message
 */
function LogEntry_create($nick, $message) {
  $timestamp = date();

  sql_query("INSERT INTO `LogEntries` SET `timestamp`=" . sql_escape($timestamp) . ", `nick`='" . sql_escape($nick) . "', `message`='" . sql_escape($message) . "'");
}


?>