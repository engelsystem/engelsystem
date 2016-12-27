<?php
use Engelsystem\ShiftsFilter;
use Engelsystem\ShiftSignupState;

function Shifts_by_room($room) {
  $result = sql_select("SELECT * FROM `Shifts` WHERE `RID`=" . sql_escape($room['RID']) . " ORDER BY `start`");
  if ($result === false) {
    engelsystem_error("Unable to load shifts.");
  }
  return $result;
}

function Shifts_by_ShiftsFilter(ShiftsFilter $shiftsFilter, $user) {
  $SQL = "SELECT `Shifts`.*, `ShiftTypes`.`name`, `Room`.`Name` as `room_name`
      FROM `Shifts`
      JOIN `Room` USING (`RID`)
      JOIN `ShiftTypes` ON `ShiftTypes`.`id` = `Shifts`.`shifttype_id`
      WHERE `Shifts`.`RID` IN (" . implode(',', $shiftsFilter->getRooms()) . ")
      AND `start` BETWEEN " . $shiftsFilter->getStartTime() . " AND " . $shiftsFilter->getEndTime() . "
      ORDER BY `Shifts`.`start`";
  /**
   * $SQL = "SELECT DISTINCT `Shifts`.*, `ShiftTypes`.`name`, `Room`.`Name` as `room_name`
   * FROM `Shifts`
   * INNER JOIN `Room` USING (`RID`)
   * INNER JOIN `ShiftTypes` ON (`ShiftTypes`.`id` = `Shifts`.`shifttype_id`)
   * LEFT JOIN (
   * SELECT COUNT(*) AS special_needs , nat3.`shift_id`
   * FROM `NeededAngelTypes` AS nat3
   * WHERE `shift_id` IS NOT NULL
   * GROUP BY nat3.`shift_id`
   * ) AS nat2 ON nat2.`shift_id` = `Shifts`.`SID`
   * INNER JOIN `NeededAngelTypes` AS nat
   * ON nat.`count` != 0
   * AND nat.`angel_type_id` IN (" . implode(',', $shiftsFilter->getTypes()) . ")
   * AND (
   * (nat2.`special_needs` > 0 AND nat.`shift_id` = `Shifts`.`SID`)
   * OR
   * (
   * (nat2.`special_needs` = 0 OR nat2.`special_needs` IS NULL)
   * AND nat.`room_id` = `RID`)
   * )
   * LEFT JOIN (
   * SELECT se.`SID`, se.`TID`, COUNT(*) as count
   * FROM `ShiftEntry` AS se GROUP BY se.`SID`, se.`TID`
   * ) AS entries ON entries.`SID` = `Shifts`.`SID` AND entries.`TID` = nat.`angel_type_id`
   * WHERE `Shifts`.`RID` IN (" . implode(',', $shiftsFilter->getRooms()) . ")
   * AND `start` BETWEEN " . $shiftsFilter->getStartTime() . " AND " . $shiftsFilter->getEndTime();
   *
   * if (count($shiftsFilter->getFilled()) == 1) {
   * if ($shiftsFilter->getFilled()[0] == ShiftsFilter::FILLED_FREE) {
   * $SQL .= "
   * AND (
   * nat.`count` > entries.`count` OR entries.`count` IS NULL
   * )";
   * } elseif ($_SESSION['user_shifts']['filled'][0] == ShiftsFilter::FILLED_FILLED) {
   * $SQL .= "
   * AND (
   * nat.`count` <= entries.`count`
   * )";
   * }
   * }
   * $SQL .= "
   * ORDER BY `start`";
   */
  $result = sql_select($SQL);
  if ($result === false) {
    engelsystem_error("Unable to load shifts by filter.");
  }
  return $result;
}

function NeededAngeltypes_by_ShiftsFilter(ShiftsFilter $shiftsFilter, $user) {
  $SQL = "SELECT `NeededAngelTypes`.*, `AngelTypes`.`id`, `AngelTypes`.`name`, `AngelTypes`.`restricted`, `AngelTypes`.`no_self_signup`
      FROM `Shifts`
      JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`
      JOIN `AngelTypes` ON `AngelTypes`.`id`= `NeededAngelTypes`.`angel_type_id`
      WHERE `Shifts`.`RID` IN (" . implode(',', $shiftsFilter->getRooms()) . ")
      AND `start` BETWEEN " . $shiftsFilter->getStartTime() . " AND " . $shiftsFilter->getEndTime() . "
      ORDER BY `Shifts`.`start`";
  // FIXME: Use needed angeltypes on rooms!
  $result = sql_select($SQL);
  if ($result === false) {
    engelsystem_error("Unable to load needed angeltypes by filter.");
  }
  return $result;
}

function NeededAngeltype_by_Shift_and_Angeltype($shift, $angeltype) {
  $result = sql_select("SELECT `NeededAngelTypes`.*, `AngelTypes`.`id`, `AngelTypes`.`name`, `AngelTypes`.`restricted`, `AngelTypes`.`no_self_signup`
      FROM `Shifts`
      JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`
      JOIN `AngelTypes` ON `AngelTypes`.`id`= `NeededAngelTypes`.`angel_type_id`
      WHERE `Shifts`.`SID`=" . sql_escape($shift['SID']) . "
      AND `AngelTypes`.`id`=" . sql_escape($angeltype['id']) . "
      ORDER BY `Shifts`.`start`");
  if ($result === false) {
    engelsystem_error("Unable to load needed angeltypes by filter.");
  }
  if (count($result) == 0) {
    $result = sql_select("SELECT `NeededAngelTypes`.*, `AngelTypes`.`id`, `AngelTypes`.`name`, `AngelTypes`.`restricted`, `AngelTypes`.`no_self_signup`
      FROM `Shifts`
      JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`room_id`=`Shifts`.`RID`
      JOIN `AngelTypes` ON `AngelTypes`.`id`= `NeededAngelTypes`.`angel_type_id`
      WHERE `Shifts`.`SID`=" . sql_escape($shift['SID']) . "
      AND `AngelTypes`.`id`=" . sql_escape($angeltype['id']) . "
      ORDER BY `Shifts`.`start`");
    if ($result === false) {
      engelsystem_error("Unable to load needed angeltypes by filter.");
    }
  }
  if(count($result) == 0) {
    return null;
  }
  return $result[0];
}

function ShiftEntries_by_ShiftsFilter(ShiftsFilter $shiftsFilter, $user) {
  $SQL = "SELECT `User`.`Nick`, `User`.`email`, `User`.`email_shiftinfo`, `User`.`Sprache`, `User`.`Gekommen`, `ShiftEntry`.`UID`, `ShiftEntry`.`TID`, `ShiftEntry`.`SID`, `ShiftEntry`.`Comment`, `ShiftEntry`.`freeloaded`
      FROM `Shifts`
      JOIN `ShiftEntry` ON `ShiftEntry`.`SID`=`Shifts`.`SID`
      JOIN `User` ON `ShiftEntry`.`UID`=`User`.`UID`
      WHERE `Shifts`.`RID` IN (" . implode(',', $shiftsFilter->getRooms()) . ")
      AND `start` BETWEEN " . $shiftsFilter->getStartTime() . " AND " . $shiftsFilter->getEndTime() . "
      ORDER BY `Shifts`.`start`";
  $result = sql_select($SQL);
  if ($result === false) {
    engelsystem_error("Unable to load shift entries by filter.");
  }
  return $result;
}

/**
 * Check if a shift collides with other shifts (in time).
 *
 * @param Shift $shift          
 * @param array<Shift> $shifts          
 */
function Shift_collides($shift, $shifts) {
  foreach ($shifts as $other_shift) {
    if ($shift['SID'] != $other_shift['SID']) {
      if (! ($shift['start'] >= $other_shift['end'] || $shift['end'] <= $other_shift['start'])) {
        return true;
      }
    }
  }
  return false;
}

/**
 * Returns the number of needed angels/free shift entries for an angeltype.
 */
function Shift_free_entries($needed_angeltype, $shift_entries) {
  $taken = 0;
  foreach ($shift_entries as $shift_entry) {
    if ($shift_entry['freeloaded'] == 0) {
      $taken ++;
    }
  }
  return max(0, $needed_angeltype['count'] - $taken);
}

/**
 * Check if shift signup is allowed from the end users point of view (no admin like privileges)
 *
 * @param Shift $shift
 *          The shift
 * @param AngelType $angeltype
 *          The angeltype to which the user wants to sign up
 * @param array<Shift> $user_shifts
 *          List of the users shifts
 * @param boolean $angeltype_supporter
 *          True, if the user has angeltype supporter rights for the angeltype, which enables him to sign somebody up for the shift.
 */
function Shift_signup_allowed_angel($user, $shift, $angeltype, $user_angeltype, $user_shifts, $needed_angeltype, $shift_entries) {
  $free_entries = Shift_free_entries($needed_angeltype, $shift_entries);
  
  if ($user['Gekommen'] == 0) {
    return new ShiftSignupState(ShiftSignupState::SHIFT_ENDED, $free_entries);
  }
  
  if ($user_shifts == null) {
    $user_shifts = Shifts_by_user($user);
  }
  
  $signed_up = false;
  foreach ($user_shifts as $user_shift) {
    if ($user_shift['SID'] == $shift['SID']) {
      $signed_up = true;
      break;
    }
  }
  
  if ($signed_up) {
    // you cannot join if you already singed up for this shift
    return new ShiftSignupState(ShiftSignupState::SIGNED_UP, $free_entries);
  }
  
  if (time() > $shift['start']) {
    // you can only join if the shift is in future
    return new ShiftSignupState(ShiftSignupState::SHIFT_ENDED, $free_entries);
  }
  if ($free_entries == 0) {
    // you cannot join if shift is full
    return new ShiftSignupState(ShiftSignupState::OCCUPIED, $free_entries);
  }
  
  if ($user_angeltype == null) {
    $user_angeltype = UserAngelType_by_User_and_AngelType($user, $angeltype);
  }
  
  if ($user_angeltype == null || ($angeltype['no_self_signup'] == 1 && $user_angeltype != null) || ($angeltype['restricted'] == 1 && $user_angeltype != null && ! isset($user_angeltype['confirm_user_id']))) {
    // you cannot join if user is not of this angel type
    // you cannot join if you are not confirmed
    // you cannot join if angeltype has no self signup
    
    return new ShiftSignupState(ShiftSignupState::ANGELTYPE, $free_entries);
  }
  
  if (Shift_collides($shift, $user_shifts)) {
    // you cannot join if user alread joined a parallel or this shift
    return new ShiftSignupState(ShiftSignupState::COLLIDES, $free_entries);
  }
  
  // Hooray, shift is free for you!
  return new ShiftSignupState(ShiftSignupState::FREE, $free_entries);
}

/**
 * Check if an angeltype supporter can sign up a user to a shift.
 */
function Shift_signup_allowed_angeltype_supporter($shift, $angeltype, $needed_angeltype, $shift_entries) {
  $free_entries = Shift_free_entries($needed_angeltype, $shift_entries);
  if ($free_entries == 0) {
    return new ShiftSignupState(ShiftSignupState::OCCUPIED, $free_entries);
  }
  
  return new ShiftSignupState(ShiftSignupState::FREE, $free_entries);
}

/**
 * Check if an admin can sign up a user to a shift.
 *
 * @param Shift $shift
 *          The shift
 * @param AngelType $angeltype
 *          The angeltype to which the user wants to sign up
 */
function Shift_signup_allowed_admin($shift, $angeltype, $needed_angeltype, $shift_entries) {
  $free_entries = Shift_free_entries($needed_angeltype, $shift_entries);
  if ($free_entries == 0) {
    // User shift admins may join anybody in every shift
    return new ShiftSignupState(ShiftSignupState::ADMIN, $free_entries);
  }
  
  return new ShiftSignupState(ShiftSignupState::FREE, $free_entries);
}

/**
 * Check if an angel can sign up for given shift.
 *
 * @param Shift $shift
 *          The shift
 * @param AngelType $angeltype
 *          The angeltype to which the user wants to sign up
 * @param array<Shift> $user_shifts
 *          List of the users shifts
 */
function Shift_signup_allowed($signup_user, $shift, $angeltype, $user_angeltype = null, $user_shifts = null, $needed_angeltype, $shift_entries) {
  global $user, $privileges;
  
  if (in_array('user_shifts_admin', $privileges)) {
    return Shift_signup_allowed_admin($shift, $angeltype, $needed_angeltype, $shift_entries);
  }
  
  if (in_array('shiftentry_edit_angeltype_supporter', $privileges) && User_is_AngelType_supporter($user, $angeltype)) {
    return Shift_signup_allowed_angeltype_supporter($shift, $angeltype, $needed_angeltype, $shift_entries);
  }
  
  return Shift_signup_allowed_angel($signup_user, $shift, $angeltype, $user_angeltype, $user_shifts, $needed_angeltype, $shift_entries);
}

/**
 * Delete a shift by its external id.
 */
function Shift_delete_by_psid($shift_psid) {
  return sql_query("DELETE FROM `Shifts` WHERE `PSID`='" . sql_escape($shift_psid) . "'");
}

/**
 * Delete a shift.
 */
function Shift_delete($shift_id) {
  mail_shift_delete(Shift($shift_id));
  
  $result = sql_query("DELETE FROM `Shifts` WHERE `SID`='" . sql_escape($shift_id) . "'");
  if ($result === false) {
    engelsystem_error('Unable to delete shift.');
  }
  return $result;
}

/**
 * Update a shift.
 */
function Shift_update($shift) {
  global $user;
  $shift['name'] = ShiftType($shift['shifttype_id'])['name'];
  mail_shift_change(Shift($shift['SID']), $shift);
  
  return sql_query("UPDATE `Shifts` SET
      `shifttype_id`='" . sql_escape($shift['shifttype_id']) . "',
      `start`='" . sql_escape($shift['start']) . "',
      `end`='" . sql_escape($shift['end']) . "',
      `RID`='" . sql_escape($shift['RID']) . "',
      `title`=" . sql_null($shift['title']) . ",
      `URL`=" . sql_null($shift['URL']) . ",
      `PSID`=" . sql_null($shift['PSID']) . ",
      `edited_by_user_id`='" . sql_escape($user['UID']) . "',
      `edited_at_timestamp`=" . time() . "
      WHERE `SID`='" . sql_escape($shift['SID']) . "'");
}

/**
 * Update a shift by its external id.
 */
function Shift_update_by_psid($shift) {
  $shift_source = sql_select("SELECT `SID` FROM `Shifts` WHERE `PSID`=" . $shift['PSID']);
  if ($shift_source === false) {
    return false;
  }
  if (count($shift_source) == 0) {
    return null;
  }
  $shift['SID'] = $shift_source[0]['SID'];
  return Shift_update($shift);
}

/**
 * Create a new shift.
 *
 * @return new shift id or false
 */
function Shift_create($shift) {
  global $user;
  $result = sql_query("INSERT INTO `Shifts` SET
      `shifttype_id`='" . sql_escape($shift['shifttype_id']) . "',
      `start`='" . sql_escape($shift['start']) . "',
      `end`='" . sql_escape($shift['end']) . "',
      `RID`='" . sql_escape($shift['RID']) . "',
      `title`=" . sql_null($shift['title']) . ",
      `URL`=" . sql_null($shift['URL']) . ",
      `PSID`=" . sql_null($shift['PSID']) . ",
      `created_by_user_id`='" . sql_escape($user['UID']) . "',
      `created_at_timestamp`=" . time());
  if ($result === false) {
    return false;
  }
  return sql_id();
}

/**
 * Return users shifts.
 */
function Shifts_by_user($user, $include_freeload_comments = false) {
  $result = sql_select("
      SELECT `ShiftTypes`.`id` as `shifttype_id`, `ShiftTypes`.`name`,
      `ShiftEntry`.`id`, `ShiftEntry`.`SID`, `ShiftEntry`.`TID`, `ShiftEntry`.`UID`, `ShiftEntry`.`freeloaded`, `ShiftEntry`.`Comment`,
      " . ($include_freeload_comments ? "`ShiftEntry`.`freeload_comment`, " : "") . "
      `Shifts`.*, `Room`.* 
      FROM `ShiftEntry` 
      JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`) 
      JOIN `ShiftTypes` ON (`ShiftTypes`.`id` = `Shifts`.`shifttype_id`)
      JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`) 
      WHERE `UID`='" . sql_escape($user['UID']) . "' 
      ORDER BY `start`
      ");
  if ($result === false) {
    engelsystem_error('Unable to load users shifts.');
  }
  return $result;
}

/**
 * Returns Shift by id.
 *
 * @param $shift_id Shift
 *          ID
 */
function Shift($shift_id) {
  $shifts_source = sql_select("
      SELECT `Shifts`.*, `ShiftTypes`.`name`
      FROM `Shifts` 
      JOIN `ShiftTypes` ON (`ShiftTypes`.`id` = `Shifts`.`shifttype_id`)
      WHERE `SID`='" . sql_escape($shift_id) . "'");
  $shiftsEntry_source = sql_select("SELECT `id`, `TID` , `UID` , `freeloaded` FROM `ShiftEntry` WHERE `SID`='" . sql_escape($shift_id) . "'");
  
  if ($shifts_source === false) {
    engelsystem_error('Unable to load shift.');
  }
  
  if (empty($shifts_source)) {
    return null;
  }
  
  $result = $shifts_source[0];
  
  $result['ShiftEntry'] = $shiftsEntry_source;
  $result['NeedAngels'] = [];
  
  $temp = NeededAngelTypes_by_shift($shift_id);
  foreach ($temp as $e) {
    $result['NeedAngels'][] = [
        'TID' => $e['angel_type_id'],
        'count' => $e['count'],
        'restricted' => $e['restricted'],
        'taken' => $e['taken'] 
    ];
  }
  
  return $result;
}

/**
 * Returns all shifts with needed angeltypes and count of subscribed jobs.
 */
function Shifts() {
  $shifts_source = sql_select("
    SELECT `ShiftTypes`.`name`, `Shifts`.*, `Room`.`RID`, `Room`.`Name` as `room_name` 
    FROM `Shifts`
    JOIN `ShiftTypes` ON (`ShiftTypes`.`id` = `Shifts`.`shifttype_id`)
    JOIN `Room` ON `Room`.`RID` = `Shifts`.`RID`
    ");
  if ($shifts_source === false) {
    return false;
  }
  
  foreach ($shifts_source as &$shift) {
    $needed_angeltypes = NeededAngelTypes_by_shift($shift['SID']);
    if ($needed_angeltypes === false) {
      return false;
    }
    
    $shift['angeltypes'] = $needed_angeltypes;
  }
  
  return $shifts_source;
}

?>
