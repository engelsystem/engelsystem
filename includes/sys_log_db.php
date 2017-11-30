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
              `entry_id`
          )
          VALUES (?, ?)
        ',
        [
            $tablename,
            (int) $entry_id
        ]
    );
}

