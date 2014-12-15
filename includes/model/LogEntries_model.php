<?php

/**
 * Creates a log entry.
 *
 * @param $nick Username
 * @param $message Log
 *          Message
 */
function LogEntry_create($nick, $message) {
  return sql_query("INSERT INTO `LogEntries` SET `timestamp`=" . sql_escape(time()) . ", `nick`='" . sql_escape($nick) . "', `message`='" . sql_escape($message) . "'");
}

/**
 * Returns log entries of the last 24 hours with maximum count of 1000.
 */
function LogEntries() {
  return sql_select("SELECT * FROM `LogEntries` ORDER BY `timestamp` DESC LIMIT 10000");
}

?>