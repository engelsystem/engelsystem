<?php

/**
 * Returns next (or current) shifts of given user.
 * @param User $user
 */
function ShiftEntries_upcoming_for_user($user) {
  return sql_select("
      SELECT *
      FROM `ShiftEntry`
      JOIN `Shifts` ON `Shifts`.`SID`=`ShiftEntry`.`SID`
      WHERE `ShiftEntry`.`UID`=" . sql_escape($user['UID']) . "
      AND `Shifts`.`end` > " . sql_escape(time()) . "
      ORDER BY `Shifts`.`end`
      ");
}

/**
 * Returns all shift entries in given shift for given angeltype.
 *
 * @param int $shift_id          
 * @param int $angeltype_id          
 */
function ShiftEntries_by_shift_and_angeltype($shift_id, $angeltype_id) {
  return sql_select("
      SELECT * 
      FROM `ShiftEntry`
      WHERE `SID`=" . sql_escape($shift_id) . "
      AND `TID`=" . sql_escape($angeltype_id) . "
      ");
}

/**
 * Returns all freeloaded shifts for given user.
 */
function ShiftEntries_freeloaded_by_user($user) {
  return sql_select("SELECT * 
      FROM `ShiftEntry` 
      WHERE `freeloaded` = 1 
      AND `UID`=" . sql_escape($user['UID']));
}

?>