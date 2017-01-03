<?php

/**
 * Creates a log entry.
 *
 * @param string $nick    Username
 * @param string $message Log  Message
 * @return mysqli_result|false
 */
function LogEntry_create($nick, $message)
{
    return sql_query("INSERT INTO `LogEntries` SET `timestamp`='" . sql_escape(time()) . "', `nick`='" . sql_escape($nick) . "', `message`='" . sql_escape($message) . "'");
}

/**
 * Returns log entries with maximum count of 10000.
 *
 * @return array|false
 */
function LogEntries()
{
    return sql_select("SELECT * FROM `LogEntries` ORDER BY `timestamp` DESC LIMIT 10000");
}

/**
 * Returns log entries filtered by a keyword
 *
 * @param string $keyword
 * @return array|false
 */
function LogEntries_filter($keyword)
{
    if ($keyword == "") {
        return LogEntries();
    }
    return sql_select("SELECT * FROM `LogEntries` WHERE `nick` LIKE '%" . sql_escape($keyword) . "%' OR `message` LIKE '%" . sql_escape($keyword) . "%' ORDER BY `timestamp` DESC");
}

/**
 * Delete all log entries.
 *
 * @return mysqli_result|false
 */
function LogEntries_clear_all()
{
    return sql_query("TRUNCATE `LogEntries`");
}
