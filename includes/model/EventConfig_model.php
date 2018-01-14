<?php

use Engelsystem\Database\DB;

/**
 * Get event config.
 *
 * @return array|null
 */
function EventConfig()
{
    $config = DB::selectOne('SELECT * FROM `EventConfig` LIMIT 1');

    return empty($config) ? null : $config;
}

/**
 * Update event config.
 *
 * @param string $event_name
 * @param int    $buildup_start_date
 * @param int    $event_start_date
 * @param int    $event_end_date
 * @param int    $teardown_end_date
 * @param string $event_welcome_msg
 * @return bool
 */
function EventConfig_update(
    $event_name,
    $buildup_start_date,
    $event_start_date,
    $event_end_date,
    $teardown_end_date,
    $event_welcome_msg
) {
    $eventConfig = EventConfig();
    if (empty($eventConfig)) {
        return DB::insert('
              INSERT INTO `EventConfig` (
                  `event_name`,
                  `buildup_start_date`,
                  `event_start_date`,
                  `event_end_date`,
                  `teardown_end_date`,
                  `event_welcome_msg`
              )
              VALUES (?, ?, ?, ?, ?, ?)
            ',
            [
                $event_name,
                $buildup_start_date,
                $event_start_date,
                $event_end_date,
                $teardown_end_date,
                $event_welcome_msg
            ]
        );
    }

    return (bool)DB::update('
          UPDATE `EventConfig` SET
          `event_name` = ?,
          `buildup_start_date` = ?,
          `event_start_date` = ?,
          `event_end_date` = ?,
          `teardown_end_date` = ?,
          `event_welcome_msg` = ?
        ',
        [
            $event_name,
            $buildup_start_date,
            $event_start_date,
            $event_end_date,
            $teardown_end_date,
            $event_welcome_msg,
        ]
    );
}
