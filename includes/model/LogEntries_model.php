<?php

/**
 * Creates a log entry.
 *
 * @param $nick Username
 * @param $message Log
 *          Message
 */
function LogEntry_create($nick, $message) {
  return sql_query("INSERT INTO `LogEntries` SET `timestamp`='" . sql_escape(time()) . "', `nick`='" . sql_escape($nick) . "', `message`='" . sql_escape($message) . "'");
}

/**
 * Returns log entries with maximum count of 10000.
 */
function LogEntries() {
  return sql_select("SELECT * FROM `LogEntries` ORDER BY `timestamp` DESC LIMIT 10000");
}

/**
 * Returns log entries filtered by a keyword
 */
function LogEntries_filter($keyword) {
  return sql_select("SELECT * FROM `LogEntries` WHERE `nick` LIKE '%" . sql_escape($keyword) . "%' OR `message` LIKE '%" . sql_escape($keyword) . "%' ORDER BY `timestamp` DESC");
}

/**
 * Delete all log entries.
 */
function LogEntries_clear_all() {
  return sql_query("TRUNCATE `LogEntries`");
}

?>
