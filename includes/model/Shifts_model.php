<?php

use Engelsystem\Database\DB;
use Engelsystem\Models\User\User;
use Engelsystem\ShiftsFilter;
use Engelsystem\ShiftSignupState;

/**
 * @param array $angeltype
 * @return array
 */
function Shifts_by_angeltype($angeltype)
{
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
 * Returns every shift with needed angels in the given time range.
 *
 * @param int $start timestamp
 * @param int $end   timestamp
 * @return array
 */
function Shifts_free($start, $end)
{
    $shifts = Db::select("
        SELECT * FROM (
            SELECT *
            FROM `Shifts`
            WHERE (`end` > ? AND `start` < ?)
            AND (SELECT SUM(`count`) FROM `NeededAngelTypes` WHERE `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`)
            > (SELECT COUNT(*) FROM `ShiftEntry` WHERE `ShiftEntry`.`SID`=`Shifts`.`SID` AND `freeloaded`=0)
            AND `Shifts`.`PSID` IS NULL
        
            UNION
        
            SELECT *
            FROM `Shifts`
            WHERE (`end` > ? AND `start` < ?)
            AND (SELECT SUM(`count`) FROM `NeededAngelTypes` WHERE `NeededAngelTypes`.`room_id`=`Shifts`.`RID`)
            > (SELECT COUNT(*) FROM `ShiftEntry` WHERE `ShiftEntry`.`SID`=`Shifts`.`SID` AND `freeloaded`=0)
            AND NOT `Shifts`.`PSID` IS NULL
        ) AS `tmp`
        ORDER BY `tmp`.`start`
        ", [
        $start,
        $end,
        $start,
        $end
    ]);
    $free_shifts = [];
    foreach ($shifts as $shift) {
        $free_shifts[] = Shift($shift['SID']);
    }
    return $free_shifts;
}

/**
 * Returns all shifts with a PSID (from frab import)
 *
 * @return array[]
 */
function Shifts_from_frab()
{
    return DB::select('SELECT * FROM `Shifts` WHERE `PSID` IS NOT NULL ORDER BY `start`');
}

/**
 * @param array $room
 * @return array[]
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

      ORDER BY `room_name`, `start`';

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
 * @return array[]
 */
function ShiftEntries_by_ShiftsFilter(ShiftsFilter $shiftsFilter)
{
    $sql = sprintf('
          SELECT
              users.*,
              `ShiftEntry`.`UID`,
              `ShiftEntry`.`TID`,
              `ShiftEntry`.`SID`,
              `ShiftEntry`.`Comment`,
              `ShiftEntry`.`freeloaded`
          FROM `Shifts`
          JOIN `ShiftEntry` ON `ShiftEntry`.`SID`=`Shifts`.`SID`
          JOIN `users` ON `ShiftEntry`.`UID`=`users`.`id`
          WHERE `Shifts`.`RID` IN (%s)
          AND `start` BETWEEN ? AND ?
          ORDER BY `Shifts`.`start`
        ',
        implode(',', $shiftsFilter->getRooms())
    );
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

    $neededAngels = !empty($needed_angeltype) ? $needed_angeltype['count'] : 0;
    return max(0, $neededAngels - $taken);
}

/**
 * Check if shift signup is allowed from the end users point of view (no admin like privileges)
 *
 * @param User       $user
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

    if (config('signup_requires_arrival') && !$user->state->arrived) {
        return new ShiftSignupState(ShiftSignupState::NOT_ARRIVED, $free_entries);
    }

    if (config('signup_advance_hours') && $shift['start'] > time() + config('signup_advance_hours') * 3600) {
        return new ShiftSignupState(ShiftSignupState::NOT_YET, $free_entries);
    }

    if (empty($user_shifts)) {
        $user_shifts = Shifts_by_user($user->id);
    }

    $signed_up = false;
    foreach ($user_shifts as $user_shift) {
        if ($user_shift['SID'] == $shift['SID']) {
            $signed_up = true;
            break;
        }
    }

    if ($signed_up) {
        // you cannot join if you already signed up for this shift
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

    if (empty($user_angeltype)) {
        $user_angeltype = UserAngelType_by_User_and_AngelType($user->id, $angeltype);
    }

    if (
        empty($user_angeltype)
        || ($angeltype['no_self_signup'] == 1 && !empty($user_angeltype))
        || ($angeltype['restricted'] == 1 && !empty($user_angeltype) && !isset($user_angeltype['confirm_user_id']))
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
 * Check if an angel can signout from a shift.
 *
 * @param array $shift           The shift
 * @param array $angeltype       The angeltype
 * @param int   $signout_user_id The user that was signed up for the shift
 * @return bool
 */
function Shift_signout_allowed($shift, $angeltype, $signout_user_id)
{
    $user = auth()->user();

    // user shifts admin can sign out any user at any time
    if (auth()->can('user_shifts_admin')) {
        return true;
    }

    // angeltype supporter can sign out any user at any time from their supported angeltype
    if (
        auth()->can('shiftentry_edit_angeltype_supporter')
        && User_is_AngelType_supporter($user, $angeltype)
    ) {
        return true;
    }

    if ($signout_user_id == $user->id && $shift['start'] > time() + config('last_unsubscribe') * 3600) {
        return true;
    }

    return false;
}

/**
 * Check if an angel can sign up for given shift.
 *
 * @param User       $signup_user
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
    if (auth()->can('user_shifts_admin')) {
        return Shift_signup_allowed_admin($needed_angeltype, $shift_entries);
    }

    if (
        auth()->can('shiftentry_edit_angeltype_supporter')
        && User_is_AngelType_supporter(auth()->user(), $angeltype)
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
}

/**
 * Delete a shift.
 *
 * @param int $shift_id
 */
function Shift_delete($shift_id)
{
    DB::delete('DELETE FROM `Shifts` WHERE `SID`=?', [$shift_id]);
    mail_shift_delete(Shift($shift_id));
}

/**
 * Update a shift.
 *
 * @param array $shift
 * @return int Updated row count
 */
function Shift_update($shift)
{
    $user = auth()->user();
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
      `edited_at_timestamp` = ?
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
            $user->id,
            time(),
            $shift['SID']
        ]
    );
}

/**
 * Update a shift by its external id.
 *
 * @param array $shift
 * @return int
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
              `created_at_timestamp`
          )
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ',
        [
            $shift['shifttype_id'],
            $shift['start'],
            $shift['end'],
            $shift['RID'],
            $shift['title'],
            $shift['URL'],
            $shift['PSID'],
            auth()->user()->id,
            time(),
            time(),
        ]
    );

    return DB::getPdo()->lastInsertId();
}

/**
 * Return users shifts.
 *
 * @param int  $userId
 * @param bool $include_freeload_comments
 * @return array[]
 */
function Shifts_by_user($userId, $include_freeload_comments = false)
{
    return DB::select('
          SELECT 
              `ShiftTypes`.`id` AS `shifttype_id`,
              `ShiftTypes`.`name`,
              `ShiftEntry`.`id`,
              `ShiftEntry`.`SID`,
              `ShiftEntry`.`TID`,
              `ShiftEntry`.`UID`,
              `ShiftEntry`.`freeloaded`,
              `ShiftEntry`.`Comment`,
              ' . ($include_freeload_comments ? '`ShiftEntry`.`freeload_comment`, ' : '') . '
              `Shifts`.*,
              `Room`.*
          FROM `ShiftEntry`
          JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`)
          JOIN `ShiftTypes` ON (`ShiftTypes`.`id` = `Shifts`.`shifttype_id`)
          JOIN `Room` ON (`Shifts`.`RID` = `Room`.`RID`)
          WHERE `UID` = ?
          ORDER BY `start`
      ',
        [
            $userId
        ]
    );
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
