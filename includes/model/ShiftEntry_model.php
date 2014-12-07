<?php

/**
 * Counts all freeloaded shifts.
 */
function ShiftEntries_freeleaded_count() {
  return sql_select_single_cell("SELECT COUNT(*) FROM `ShiftEntry` WHERE `freeloaded` = 1");
}

/**
 * List users subsribed to a given shift.
 */
function ShiftEntries_by_shift($shift_id) {
  return sql_select("
      SELECT `User`.`Nick`, `User`.`email`, `User`.`email_shiftinfo`, `User`.`Sprache`, `ShiftEntry`.`UID`, `ShiftEntry`.`TID`, `ShiftEntry`.`SID`, `AngelTypes`.`name` as `angel_type_name`, `ShiftEntry`.`Comment`, `ShiftEntry`.`freeloaded`
      FROM `ShiftEntry`
      JOIN `User` ON `ShiftEntry`.`UID`=`User`.`UID`
      JOIN `AngelTypes` ON `ShiftEntry`.`TID`=`AngelTypes`.`id`
      WHERE `ShiftEntry`.`SID`=" . sql_escape($shift_id));
}

/**
 * Create a new shift entry.
 *
 * @param ShiftEntry $shift_entry          
 */
function ShiftEntry_create($shift_entry) {
  return sql_query("INSERT INTO `ShiftEntry` SET
      `SID`=" . sql_escape($shift_entry['SID']) . ",
      `TID`=" . sql_escape($shift_entry['TID']) . ",
      `UID`=" . sql_escape($shift_entry['UID']) . ",
      `Comment`='" . sql_escape($shift_entry['Comment']) . "',
      `freeload_comment`='" . sql_escape($shift_entry['freeload_comment']) . "',
      `freeloaded`=" . sql_escape($shift_entry['freeloaded'] ? 'TRUE' : 'FALSE'));
}

/**
 * Delete a shift entry.
 */
function ShiftEntry_delete($shift_entry_id) {
  return sql_query("DELETE FROM `ShiftEntry` WHERE `id`=" . sql_escape($shift_entry_id));
}

/**
 * Returns next (or current) shifts of given user.
 *
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