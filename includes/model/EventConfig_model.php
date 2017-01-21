<?php

use Engelsystem\Database\DB;

/**
 * Get event config.
 *
 * @return array|null
 */
function EventConfig()
{
    $event_config = DB::select('SELECT * FROM `EventConfig` LIMIT 1');
    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to load event config.');
        return null;
    }

    if (empty($event_config)) {
        return null;
    }

    return array_shift($event_config);
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
    if (EventConfig() == null) {
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
