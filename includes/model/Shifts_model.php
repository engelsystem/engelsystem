<?php

use Engelsystem\Database\DB;
use Engelsystem\ShiftsFilter;
use Engelsystem\ShiftSignupState;

/**
 * @param array $angeltype
 * @return array
 */
function Shifts_by_angeltype($angeltype) {
    return DB::select('
        SELECT DISTINCT `Shifts`.* FROM `Shifts`
        JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`shift_id` = `Shifts`.`SID`
        WHERE `NeededAngelTypes`.`angel_type_id` = ?
        AND `NeededAngelTypes`.`count` > 0
        AND `Shifts`.`PSID` IS NULL
        
        UNION
        
        SELECT DISTINCT `Shifts`.* FROM `Shifts`
        JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`room_id` = `Shifts`.`RID`
        WHERE `NeededAngelTypes`.`angel_type_id` = ?
        AND `NeededAngelTypes`.`count` > 0
        AND NOT `Shifts`.`PSID` IS NULL
        ', [$angeltype['id'], $angeltype['id']]);
}


/**
 * @param array $room
 * @return array
 */
function Shifts_by_room($room)
{
    return DB::select('SELECT * FROM `Shifts` WHERE `RID`=? ORDER BY `start`', [$room['RID']]);
}

/**
 * @param ShiftsFilter $shiftsFilter
 * @return array[]
 */
function Shifts_by_ShiftsFilter(ShiftsFilter $shiftsFilter)
{
    $sql = 'SELECT * FROM (
      SELECT DISTINCT `Shifts`.*, `ShiftTypes`.`name`, `Room`.`Name` AS `room_name`
      FROM `Shifts`
      JOIN `Room` USING (`RID`)
      JOIN `ShiftTypes` ON `ShiftTypes`.`id` = `Shifts`.`shifttype_id`
      JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`shift_id` = `Shifts`.`SID`
      WHERE `Shifts`.`RID` IN (' . implode(',', $shiftsFilter->getRooms()) . ')
      AND `start` BETWEEN ? AND ?
      AND `NeededAngelTypes`.`angel_type_id` IN (' . implode(',', $shiftsFilter->getTypes()) . ')
      AND `NeededAngelTypes`.`count` > 0
      AND `Shifts`.`PSID` IS NULL

      UNION

      SELECT DISTINCT `Shifts`.*, `ShiftTypes`.`name`, `Room`.`Name` AS `room_name`
      FROM `Shifts`
      JOIN `Room` USING (`RID`)
      JOIN `ShiftTypes` ON `ShiftTypes`.`id` = `Shifts`.`shifttype_id`
      JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`room_id`=`Shifts`.`RID`
      WHERE `Shifts`.`RID` IN (' . implode(',', $shiftsFilter->getRooms()) . ')
      AND `start` BETWEEN ? AND ?
      AND `NeededAngelTypes`.`angel_type_id` IN (' . implode(',', $shiftsFilter->getTypes()) . ')
      AND `NeededAngelTypes`.`count` > 0
      AND NOT `Shifts`.`PSID` IS NULL) AS tmp_shifts

      ORDER BY `start`';

    return DB::select(
        $sql,
        [
            $shiftsFilter->getStartTime(),
            $shiftsFilter->getEndTime(),
            $shiftsFilter->getStartTime(),
            $shiftsFilter->getEndTime(),
        ]
    );
}

/**
 * @param ShiftsFilter $shiftsFilter
 * @return array[]
 */
function NeededAngeltypes_by_ShiftsFilter(ShiftsFilter $shiftsFilter)
{
    $sql = '
      SELECT
          `NeededAngelTypes`.*,
          `Shifts`.`SID`,
          `AngelTypes`.`id`,
          `AngelTypes`.`name`,
          `AngelTypes`.`restricted`,
          `AngelTypes`.`no_self_signup`
      FROM `Shifts`
      JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`
      JOIN `AngelTypes` ON `AngelTypes`.`id`= `NeededAngelTypes`.`angel_type_id`
      WHERE `Shifts`.`RID` IN (' . implode(',', $shiftsFilter->getRooms()) . ')
      AND `start` BETWEEN ? AND ?
      AND `Shifts`.`PSID` IS NULL

      UNION

      SELECT
            `NeededAngelTypes`.*,
            `Shifts`.`SID`,
            `AngelTypes`.`id`,
            `AngelTypes`.`name`,
            `AngelTypes`.`restricted`,
            `AngelTypes`.`no_self_signup`
      FROM `Shifts`
      JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`room_id`=`Shifts`.`RID`
      JOIN `AngelTypes` ON `AngelTypes`.`id`= `NeededAngelTypes`.`angel_type_id`
      WHERE `Shifts`.`RID` IN (' . implode(',', $shiftsFilter->getRooms()) . ')
      AND `start` BETWEEN ? AND ?
      AND NOT `Shifts`.`PSID` IS NULL';

    return DB::select(
        $sql,
        [
            $shiftsFilter->getStartTime(),
            $shiftsFilter->getEndTime(),
            $shiftsFilter->getStartTime(),
            $shiftsFilter->getEndTime(),
        ]
    );
}

/**
 * @param array $shift
 * @param array $angeltype
 * @return array|null
 */
function NeededAngeltype_by_Shift_and_Angeltype($shift, $angeltype)
{
    return DB::selectOne('
          SELECT
              `NeededAngelTypes`.*,
              `Shifts`.`SID`,
              `AngelTypes`.`id`,
              `AngelTypes`.`name`,
              `AngelTypes`.`restricted`,
              `AngelTypes`.`no_self_signup`
          FROM `Shifts`
          JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`
          JOIN `AngelTypes` ON `AngelTypes`.`id`= `NeededAngelTypes`.`angel_type_id`
          WHERE `Shifts`.`SID`=?
          AND `AngelTypes`.`id`=?
          AND `Shifts`.`PSID` IS NULL

          UNION

          SELECT
                `NeededAngelTypes`.*,
                `Shifts`.`SID`,
                `AngelTypes`.`id`,
                `AngelTypes`.`name`,
                `AngelTypes`.`restricted`,
                `AngelTypes`.`no_self_signup`
          FROM `Shifts`
          JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`room_id`=`Shifts`.`RID`
          JOIN `AngelTypes` ON `AngelTypes`.`id`= `NeededAngelTypes`.`angel_type_id`
          WHERE `Shifts`.`SID`=?
          AND `AngelTypes`.`id`=?
          AND NOT `Shifts`.`PSID` IS NULL
      ',
        [
            $shift['SID'],
            $angeltype['id'],
            $shift['SID'],
            $angeltype['id']
        ]
    );
}

/**
 * @param ShiftsFilter $shiftsFilter
 * @return array
 */
function ShiftEntries_by_ShiftsFilter(ShiftsFilter $shiftsFilter)
{
    $sql = '
      SELECT
          `User`.`Nick`,
          `User`.`email`,
          `User`.`email_shiftinfo`,
          `User`.`Sprache`,
          `User`.`Gekommen`,
          `ShiftEntry`.`UID`,
          `ShiftEntry`.`TID`,
          `ShiftEntry`.`SID`,
          `ShiftEntry`.`Comment`,
          `ShiftEntry`.`freeloaded`
      FROM `Shifts`
      JOIN `ShiftEntry` ON `ShiftEntry`.`SID`=`Shifts`.`SID`
      JOIN `User` ON `ShiftEntry`.`UID`=`User`.`UID`
      WHERE `Shifts`.`RID` IN (' . implode(',', $shiftsFilter->getRooms()) . ')
      AND `start` BETWEEN ? AND ?
      ORDER BY `Shifts`.`start`';
    return DB::select(
        $sql,
        [
            $shiftsFilter->getStartTime(),
            $shiftsFilter->getEndTime(),
        ]
    );
}

/**
 * Check if a shift collides with other shifts (in time).
 *
 * @param array $shift
 * @param array $shifts
 * @return bool
 */
function Shift_collides($shift, $shifts)
{
    foreach ($shifts as $other_shift) {
        if ($shift['SID'] != $other_shift['SID']) {
            if (!($shift['start'] >= $other_shift['end'] || $shift['end'] <= $other_shift['start'])) {
                return true;
            }
        }
    }
    return false;
}

/**
 * Returns the number of needed angels/free shift entries for an angeltype.
 *
 * @param array   $needed_angeltype
 * @param array[] $shift_entries
 * @return int
 */
function Shift_free_entries($needed_angeltype, $shift_entries)
{
    $taken = 0;
    foreach ($shift_entries as $shift_entry) {
        if ($shift_entry['freeloaded'] == 0) {
            $taken++;
        }
    }
    return max(0, $needed_angeltype['count'] - $taken);
}

/**
 * Check if shift signup is allowed from the end users point of view (no admin like privileges)
 *
 * @param array      $user
 * @param array      $shift       The shift
 * @param array      $angeltype   The angeltype to which the user wants to sign up
 * @param array|null $user_angeltype
 * @param array|null $user_shifts List of the users shifts
 * @param array      $needed_angeltype
 * @param array[]    $shift_entries
 * @return ShiftSignupState
 */
function Shift_signup_allowed_angel(
    $user,
    $shift,
    $angeltype,
    $user_angeltype,
    $user_shifts,
    $needed_angeltype,
    $shift_entries
) {
    $free_entries = Shift_free_entries($needed_angeltype, $shift_entries);

    if (config('signup_requires_arrival') && !$user['Gekommen']) {
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

    if (
        $user_angeltype == null
        || ($angeltype['no_self_signup'] == 1 && $user_angeltype != null)
        || ($angeltype['restricted'] == 1 && $user_angeltype != null && !isset($user_angeltype['confirm_user_id']))
    ) {
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
 *
 * @param array   $needed_angeltype
 * @param array[] $shift_entries
 * @return ShiftSignupState
 */
function Shift_signup_allowed_angeltype_supporter($needed_angeltype, $shift_entries)
{
    $free_entries = Shift_free_entries($needed_angeltype, $shift_entries);
    if ($free_entries == 0) {
        return new ShiftSignupState(ShiftSignupState::OCCUPIED, $free_entries);
    }

    return new ShiftSignupState(ShiftSignupState::FREE, $free_entries);
}

/**
 * Check if an admin can sign up a user to a shift.
 *
 * @param array   $needed_angeltype
 * @param array[] $shift_entries
 * @return ShiftSignupState
 */
function Shift_signup_allowed_admin($needed_angeltype, $shift_entries)
{
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
 * @param array      $signup_user
 * @param array      $shift       The shift
 * @param array      $angeltype   The angeltype to which the user wants to sign up
 * @param array|null $user_angeltype
 * @param array|null $user_shifts List of the users shifts
 * @param array      $needed_angeltype
 * @param array[]    $shift_entries
 * @return ShiftSignupState
 */
function Shift_signup_allowed(
    $signup_user,
    $shift,
    $angeltype,
    $user_angeltype,
    $user_shifts,
    $needed_angeltype,
    $shift_entries
) {
    global $user, $privileges;

    if (in_array('user_shifts_admin', $privileges)) {
        return Shift_signup_allowed_admin($needed_angeltype, $shift_entries);
    }

    if (
        in_array('shiftentry_edit_angeltype_supporter', $privileges)
        && User_is_AngelType_supporter($user, $angeltype)
    ) {
        return Shift_signup_allowed_angeltype_supporter($needed_angeltype, $shift_entries);
    }

    return Shift_signup_allowed_angel(
        $signup_user,
        $shift,
        $angeltype,
        $user_angeltype,
        $user_shifts,
        $needed_angeltype,
        $shift_entries
    );
}

/**
 * Delete a shift by its external id.
 *
 * @param int $shift_psid
 */
function Shift_delete_by_psid($shift_psid)
{
    DB::delete('DELETE FROM `Shifts` WHERE `PSID`=?', [$shift_psid]);
    db_log_delete('shifts_psid', $shift_psid);
}

/**
 * Delete a shift.
 *
 * @param int $shift_id
 */
function Shift_delete($shift_id)
{
    mail_shift_delete(Shift($shift_id));

    DB::delete('DELETE FROM `Shifts` WHERE `SID`=?', [$shift_id]);
    db_log_delete('shifts', $shift_id);
}

/**
 * Update a shift.
 *
 * @param array $shift
 * @return int Updated row count
 */
function Shift_update($shift)
{
    global $user;
    $shift['name'] = ShiftType($shift['shifttype_id'])['name'];
    mail_shift_change(Shift($shift['SID']), $shift);

    return DB::update('
      UPDATE `Shifts` SET
      `shifttype_id` = ?,
      `start` = ?,
      `end` = ?,
      `RID` = ?,
      `title` = ?,
      `URL` = ?,
      `PSID` = ?,
      `edited_by_user_id` = ?,
      `edited_at_timestamp` = ?,
      `updated_microseconds` = ?
      WHERE `SID` = ?
    ',
        [
            $shift['shifttype_id'],
            $shift['start'],
            $shift['end'],
            $shift['RID'],
            $shift['title'],
            $shift['URL'],
            $shift['PSID'],
            $user['UID'],
            time(),
            time_microseconds(),
            $shift['SID'],
        ]
    );
}

/**
 * Update a shift by its external id.
 *
 * @param array $shift
 * @return bool|null
 * @throws Exception
 */
function Shift_update_by_psid($shift)
{
    $shift_source = DB::selectOne('SELECT `SID` FROM `Shifts` WHERE `PSID`=?', [$shift['PSID']]);

    if (empty($shift_source)) {
        throw new Exception('Shift not found.');
    }

    $shift['SID'] = $shift_source['SID'];
    return Shift_update($shift);
}

/**
 * Create a new shift.
 *
 * @param array $shift
 * @return int ID of the new created shift
 */
function Shift_create($shift)
{
    global $user;
    DB::insert('
          INSERT INTO `Shifts` (
              `shifttype_id`,
              `start`,
              `end`,
              `RID`,
              `title`,
              `URL`,
              `PSID`,
              `created_by_user_id`,
              `edited_at_timestamp`,
              `created_at_timestamp`,
              `updated_microseconds`
          )
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ',
        [
            $shift['shifttype_id'],
            $shift['start'],
            $shift['end'],
            $shift['RID'],
            $shift['title'],
            $shift['URL'],
            $shift['PSID'],
            $user['UID'],
            time(),
            time(),
            time_microseconds(),
        ]
    );

    return DB::getPdo()->lastInsertId();
}

/**
 * Return users shifts.
 *
 * @param array $user
 * @param bool  $include_freeload_comments
 * @return array
 */
function Shifts_by_user($user, $include_freeload_comments = false)
{
    return DB::select('
          SELECT `ShiftTypes`.`id` AS `shifttype_id`, `ShiftTypes`.`name`,
          `ShiftEntry`.`id`, `ShiftEntry`.`SID`, `ShiftEntry`.`TID`, `ShiftEntry`.`UID`, `ShiftEntry`.`freeloaded`, `ShiftEntry`.`Comment`,
          ' . ($include_freeload_comments ? '`ShiftEntry`.`freeload_comment`, ' : '') . '
          `Shifts`.*, `Room`.*
          FROM `ShiftEntry`
          JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`)
          JOIN `ShiftTypes` ON (`ShiftTypes`.`id` = `Shifts`.`shifttype_id`)
          JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`)
          WHERE `UID` = ?
          ORDER BY `start`
      ',
        [
            $user['UID']
        ]
    );
}

/**
 * Return users shifts.
 */
function Shifts_for_websql($since, $deleted_lastid) {

    $limit = 2000; // 5k items per fetch gives ~1MB large json-response

    // fetch shifts count
  $shifts_count = DB::select("
      SELECT COUNT(SID) as count
      FROM Shifts
      WHERE SID > ?
      ",
      [
        $since['shifts']
      ]
  );
  if ($shifts_count === false) {
    engelsystem_error('Unable to load websql shifts_count.');
  }
  $shifts_count = $shifts_count[0]['count'];

    // fetch shifts
  $shifts = DB::select("
      SELECT SID, title, shifttype_id, start, end, RID
      FROM Shifts
      WHERE SID > ?
      ORDER BY SID ASC
      LIMIT " . $limit . "
      ",
      [
        $since['shifts']
      ]
  );
  if ($shifts === false) {
    engelsystem_error('Unable to load websql shifts.');
  }



    // fetch shift types count
  $shift_types_count = DB::select("
      SELECT COUNT(id) as count
      FROM ShiftTypes
      WHERE id > ?
      ",
      [
        $since['shift_types']
      ]
  );
  if ($shift_types_count === false) {
    engelsystem_error('Unable to load websql shift_types_count.');
  }
  $shift_types_count = $shift_types_count[0]['count'];

    // fetch shift types
  $shift_types = DB::select("
      SELECT id, name, angeltype_id
      FROM ShiftTypes
      WHERE id > ?
      ORDER BY id ASC
      LIMIT " . $limit . "
      ",
      [
        $since['shift_types']
      ]
  );
  if ($shift_types === false) {
    engelsystem_error('Unable to load websql shift_types.');
  }



    // fetch rooms count
  $rooms_count = DB::select("
      SELECT COUNT(RID) as count
      FROM Room
      WHERE RID > ?
      ",
      [
        $since['rooms']
      ]
  );
  if ($rooms_count === false) {
    engelsystem_error('Unable to load websql rooms_count.');
  }
  $rooms_count = $rooms_count[0]['count'];

    // fetch rooms
  $rooms = DB::select("
      SELECT RID, Name
      FROM Room
      WHERE RID > ?
      ORDER BY RID ASC
      LIMIT " . $limit . "
      ",
      [
        $since['rooms']
      ]
  );
  if ($rooms === false) {
    engelsystem_error('Unable to load websql rooms.');
  }



    // fetch shift_entries count
  $shift_entries_count = DB::select("
      SELECT COUNT(id) as count
      FROM ShiftEntry
      WHERE id > ?
      ",
      [
        $since['shift_entries']
      ]
  );
  if ($shift_entries_count === false) {
    engelsystem_error('Unable to load websql shift_entries_count.');
  }
  $shift_entries_count = $shift_entries_count[0]['count'];

    // fetch shift_entries
  $shift_entries = DB::select("
      SELECT id, SID, TID, UID, freeloaded
      FROM ShiftEntry
      WHERE id > ?
      ORDER BY id ASC
      LIMIT " . $limit . "
      ",
      [
        $since['shift_entries']
      ]
  );
  if ($shift_entries === false) {
    engelsystem_error('Unable to load websql shift_entries_count.');
  }



    // fetch users count
  $users_count = DB::select("
      SELECT COUNT(UID) as count
      FROM User
      WHERE Gekommen = '1'
      AND UID > ?
      ",
      [
        $since['users']
      ]
  );
  if ($users_count === false) {
    engelsystem_error('Unable to load websql users_count.');
  }
  $users_count = $users_count[0]['count'];

    // fetch users
  $users = DB::select("
      SELECT UID, Nick
      FROM User
      WHERE Gekommen = '1'
      AND UID > ?
      ORDER BY UID ASC
      LIMIT " . $limit . "
      ",
      [
        $since['users']
      ]
  );
  if ($users === false) {
    engelsystem_error('Unable to load websql users.');
  }



    // fetch angel types count
  $angeltypes_count = DB::select("
      SELECT COUNT(id) as count
      FROM AngelTypes
      WHERE id > ?
      ",
      [
        $since['angeltypes']
      ]
  );
  if ($angeltypes_count === false) {
    engelsystem_error('Unable to load websql angeltypes_count.');
  }
  $angeltypes_count = $angeltypes_count[0]['count'];

    // fetch angel types
  $angeltypes = DB::select("
      SELECT id, name, restricted, no_self_signup
      FROM AngelTypes
      WHERE id > ?
      ORDER BY id ASC
      LIMIT " . $limit . "
      ",
      [
        $since['angeltypes']
      ]
  );
  if ($angeltypes === false) {
    engelsystem_error('Unable to load websql angeltypes.');
  }



    // fetch needed angel types count
  $needed_angeltypes_count = DB::select("
      SELECT COUNT(id) as count
      FROM NeededAngelTypes
      WHERE id > ?
      ",
      [
        $since['needed_angeltypes']
      ]
  );
  if ($needed_angeltypes_count === false) {
    engelsystem_error('Unable to load websql needed_angeltypes_count.');
  }
  $needed_angeltypes_count = $needed_angeltypes_count[0]['count'];

    // fetch needed angel types
  $needed_angeltypes = DB::select("
      SELECT id, room_id as RID, shift_id as SID, angel_type_id as ATID, count
      FROM NeededAngelTypes
      WHERE id > ?
      ORDER BY id ASC
      LIMIT " . $limit . "
      ",
      [
        $since['needed_angeltypes']
      ]
  );
  if ($needed_angeltypes === false) {
    engelsystem_error('Unable to load websql needed_angeltypes.');
  }

    // fetch deleted entries
  $all_deleted_entries = DB::select("
      SELECT id, tablename, entry_id
      FROM DeleteLog
      WHERE id > ?
      ORDER BY id ASC
      LIMIT " . $limit . "
      ",
      [
        $deleted_lastid
      ]
  );
  if ($all_deleted_entries === false) {
    engelsystem_error('Unable to load websql deleted_entries.');
  }

  // build array
  $deleted_entries = array();
  foreach ($all_deleted_entries as $e) {
      $k = $e['tablename'];
      $v = $e['entry_id'];
      if(!array_key_exists($k, $deleted_entries)) {
        $deleted_entries[$k] = array();
      }
      array_push($deleted_entries[$k], $v);
  }

  // simplify it for js
  $deleted_entries_simplified = array();
  foreach ($deleted_entries as $key => $values) {
      array_push($deleted_entries_simplified, array(
        'tablename' => $key,
        'entry_ids' => $values,
      ));
  }

  if (count($all_deleted_entries) > 0) {
      $last = count($all_deleted_entries) - 1;
      $deleted_entries_lastid = $all_deleted_entries[$last]['id'];
  } else {
      $deleted_entries_lastid = false;
  }



  $result = array(
    'shift_types' => $shift_types,
    'shift_types_total' => $shift_types_count,
    'angeltypes' => $angeltypes,
    'angeltypes_total' => $angeltypes_count,
    'rooms' => $rooms,
    'rooms_total' => $rooms_count,
    'users' => $users,
    'users_total' => $users_count,
    'shift_entries' => $shift_entries,
    'shift_entries_total' => $shift_entries_count,
    'shifts' => $shifts,
    'shifts_total' => $shifts_count,
    'needed_angeltypes' => $needed_angeltypes,
    'needed_angeltypes_total' => $needed_angeltypes_count,
    'deleted_entries' => $deleted_entries_simplified,
    'deleted_entries_lastid' => $deleted_entries_lastid,
  );
  return $result;
}

/**
 * Returns Shift by id.
 *
 * @param int $shift_id Shift  ID
 * @return array|null
 */
function Shift($shift_id)
{
    $result = DB::selectOne('
      SELECT `Shifts`.*, `ShiftTypes`.`name`
      FROM `Shifts`
      JOIN `ShiftTypes` ON (`ShiftTypes`.`id` = `Shifts`.`shifttype_id`)
      WHERE `SID`=?', [$shift_id]);

    if (empty($result)) {
        return null;
    }

    $shiftsEntry_source = DB::select('
        SELECT `id`, `TID` , `UID` , `freeloaded`
        FROM `ShiftEntry`
        WHERE `SID`=?', [$shift_id]);

    $result['ShiftEntry'] = $shiftsEntry_source;
    $result['NeedAngels'] = [];

    $angelTypes = NeededAngelTypes_by_shift($shift_id);
    foreach ($angelTypes as $type) {
        $result['NeedAngels'][] = [
            'TID'        => $type['angel_type_id'],
            'count'      => $type['count'],
            'restricted' => $type['restricted'],
            'taken'      => $type['taken']
        ];
    }

    return $result;
}

/**
 * Returns all shifts with needed angeltypes and count of subscribed jobs.
 *
 * @return array
 */
function Shifts()
{
    $shifts_source = DB::select('
        SELECT `ShiftTypes`.`name`, `Shifts`.*, `Room`.`RID`, `Room`.`Name` AS `room_name`
        FROM `Shifts`
        JOIN `ShiftTypes` ON (`ShiftTypes`.`id` = `Shifts`.`shifttype_id`)
        JOIN `Room` ON `Room`.`RID` = `Shifts`.`RID`
    ');

    foreach ($shifts_source as &$shift) {
        $needed_angeltypes = NeededAngelTypes_by_shift($shift['SID']);
        $shift['angeltypes'] = $needed_angeltypes;
    }

    return $shifts_source;
}
