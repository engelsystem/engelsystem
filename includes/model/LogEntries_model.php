<?php

use Engelsystem\Database\DB;

/**
 * Creates a log entry.
 *
 * @param string $nick    Username
 * @param string $message Log  Message
 * @return bool
 */
function LogEntry_create($nick, $message)
{
    return DB::insert('
        INSERT INTO `LogEntries` (`timestamp`, `nick`, `message`)
        VALUES(?, ?, ?)
    ', [time(), $nick, $message]);
}

/**
 * Returns log entries with maximum count of 10000.
 *
 * @return array
 */
function LogEntries()
{
    return DB::select('SELECT * FROM `LogEntries` ORDER BY `timestamp` DESC LIMIT 10000');
}

/**
 * Returns log entries filtered by a keyword
 *
 * @param string $keyword
 * @return array
 */
function LogEntries_filter($keyword)
{
    if ($keyword == '') {
        return LogEntries();
    }

    $keyword = '%' . $keyword . '%';
    return DB::select('
            SELECT *
            FROM `LogEntries`
            WHERE `nick` LIKE ?
            OR `message` LIKE ?
            ORDER BY `timestamp` DESC
        ',
        [$keyword, $keyword]
    );
}

/**
 * Delete all log entries.
 *
 * @return bool
 */
function LogEntries_clear_all()
{
    return DB::statement('TRUNCATE `LogEntries`');
}
