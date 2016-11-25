<?php

/**
 * Returns an array with the attributes of shift entries.
 * FIXME! Needs entity object.
 */
function ShiftEntry_new() {
  return [
      'id' => null,
      'SID' => null,
      'TID' => null,
      'UID' => null,
      'Comment' => null,
      'freeloaded_comment' => null,
      'freeloaded' => false 
  ];
}

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
      WHERE `ShiftEntry`.`SID`='" . sql_escape($shift_id) . "'");
}

/**
 * Create a new shift entry.
 *
 * @param ShiftEntry $shift_entry          
 */
function ShiftEntry_create($shift_entry) {
  mail_shift_assign(User($shift_entry['UID']), Shift($shift_entry['SID']));
  return sql_query("INSERT INTO `ShiftEntry` SET
      `SID`='" . sql_escape($shift_entry['SID']) . "',
      `TID`='" . sql_escape($shift_entry['TID']) . "',
      `UID`='" . sql_escape($shift_entry['UID']) . "',
      `Comment`='" . sql_escape($shift_entry['Comment']) . "',
      `freeload_comment`='" . sql_escape($shift_entry['freeload_comment']) . "',
      `freeloaded`=" . sql_bool($shift_entry['freeloaded']));
}

/**
 * Update a shift entry.
 */
function ShiftEntry_update($shift_entry) {
  return sql_query("UPDATE `ShiftEntry` SET
      `Comment`='" . sql_escape($shift_entry['Comment']) . "',
      `freeload_comment`='" . sql_escape($shift_entry['freeload_comment']) . "',
      `freeloaded`=" . sql_bool($shift_entry['freeloaded']) . "
      WHERE `id`='" . sql_escape($shift_entry['id']) . "'");
}

/**
 * Get a shift entry.
 */
function ShiftEntry($shift_entry_id) {
  $shift_entry = sql_select("SELECT * FROM `ShiftEntry` WHERE `id`='" . sql_escape($shift_entry_id) . "'");
  if ($shift_entry === false) {
    return false;
  }
  if (count($shift_entry) == 0) {
    return null;
  }
  return $shift_entry[0];
}

/**
 * Delete a shift entry.
 */
function ShiftEntry_delete($shift_entry_id) {
  $shift_entry = ShiftEntry($shift_entry_id);
  mail_shift_removed(User($shift_entry['UID']), Shift($shift_entry['SID']));
  return sql_query("DELETE FROM `ShiftEntry` WHERE `id`='" . sql_escape($shift_entry_id) . "'");
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
      JOIN `Shifts` ON (`Shifts`.`SID` = `ShiftEntry`.`SID`)
      JOIN `ShiftTypes` ON `ShiftTypes`.`id` = `Shifts`.`shifttype_id`
      WHERE `ShiftEntry`.`UID`=" . sql_escape($user['UID']) . "
      AND `Shifts`.`end` > " . sql_escape(time()) . "
      ORDER BY `Shifts`.`end`
      ");
}

/**
 * Returns shifts completed by the given user.
 *
 * @param User $user          
 */
function ShiftEntries_finished_by_user($user) {
  return sql_select("
      SELECT *
      FROM `ShiftEntry`
      JOIN `Shifts` ON (`Shifts`.`SID` = `ShiftEntry`.`SID`)
      JOIN `ShiftTypes` ON `ShiftTypes`.`id` = `Shifts`.`shifttype_id`
      WHERE `ShiftEntry`.`UID`=" . sql_escape($user['UID']) . "
      AND `Shifts`.`end` < " . sql_escape(time()) . "
      AND `ShiftEntry`.`freeloaded` = 0
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
  $result = sql_select("
      SELECT *
      FROM `ShiftEntry`
      WHERE `SID`=" . sql_escape($shift_id) . "
      AND `TID`=" . sql_escape($angeltype_id) . "
      ");
  if ($result === false) {
    engelsystem_error("Unable to load shift entries.");
  }
  return $result;
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
