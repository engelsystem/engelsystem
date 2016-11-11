<?php

/**
 * Get event config.
 */
function EventConfig() {
  $event_config = sql_select("SELECT * FROM `EventConfig` LIMIT 1");
  if ($event_config === false) {
    engelsystem_error("Unable to load event config.");
    return false;
  }
  if (count($event_config) > 0) {
    return $event_config[0];
  }
  return null;
}

/**
 * Update event config.
 *
 * @param string $event_name          
 * @param int $buildup_start_date          
 * @param int $event_start_date          
 * @param int $event_end_date          
 * @param int $teardown_end_date          
 * @param string $event_welcome_msg          
 */
function EventConfig_update($event_name, $buildup_start_date, $event_start_date, $event_end_date, $teardown_end_date, $event_welcome_msg) {
  if (EventConfig() == null) {
    return sql_query("INSERT INTO `EventConfig` SET
      `event_name`=" . sql_null($event_name) . ",
      `buildup_start_date`=" . sql_null($buildup_start_date) . ",
      `event_start_date`=" . sql_null($event_start_date) . ",
      `event_end_date`=" . sql_null($event_end_date) . ",
      `teardown_end_date`=" . sql_null($teardown_end_date) . ",
      `event_welcome_msg`=" . sql_null($event_welcome_msg));
  }
  return sql_query("UPDATE `EventConfig` SET
      `event_name`=" . sql_null($event_name) . ", 
      `buildup_start_date`=" . sql_null($buildup_start_date) . ",
      `event_start_date`=" . sql_null($event_start_date) . ",
      `event_end_date`=" . sql_null($event_end_date) . ",
      `teardown_end_date`=" . sql_null($teardown_end_date) . ",        
      `event_welcome_msg`=" . sql_null($event_welcome_msg));
}
?>
