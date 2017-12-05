<?php

use Engelsystem\Database\DB;

/**
 * Keep track of deleted entries
 * Needed for proper synchronisation of websql
 *
 * @param string $tablename
 * @param int $entry_id
 */
function db_log_delete($tablename, $entry_id)
{
    DB::insert('
          INSERT INTO `DeleteLog` (
              `tablename`,
              `entry_id`,
              `updated_microseconds`
          )
          VALUES (?, ?, ?)
        ',
        [
            $tablename,
            (int) $entry_id,
            time_microseconds(),
        ]
    );
}

/**
 * Make sure you have set precision = 16 in php.ini!
 * Needed for exact tracking of updates for synchronisation with websql
 */
function time_microseconds()
{
    list($usec, $sec) = explode(' ', microtime());
    return ((float)$usec + (float)$sec);
}

