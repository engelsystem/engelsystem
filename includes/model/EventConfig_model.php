<?php

/**
 * Get settings.
 */
function EventConfig() {
  $settings = sql_select("SELECT * FROM `EventConfig` LIMIT 1");
  if ($settings === false)
    return false;
  if (count($settings) > 0)
    return $settings[0];
  return null;
}

/**
 * Update Settings.
 *
 * @param string $event_name          
 * @param int $buildup_start_date          
 * @param int $event_start_date          
 * @param int $event_end_date          
 * @param int $teardown_end_date          
 * @param string $event_welcome_msg          
 */
function EventConfig_update($event_name, $buildup_start_date, $event_start_date, $event_end_date, $teardown_end_date, $event_welcome_msg) {
  if (Settings() == null) {
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
