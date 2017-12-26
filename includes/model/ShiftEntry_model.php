<?php

use Engelsystem\Database\DB;

/**
 * Returns an array with the attributes of shift entries.
 * FIXME! Needs entity object.
 *
 * @return array
 */
function ShiftEntry_new()
{
    return [
        'id'                 => null,
        'SID'                => null,
        'TID'                => null,
        'UID'                => null,
        'Comment'            => null,
        'freeloaded_comment' => null,
        'freeloaded'         => false
    ];
}

/**
 * Counts all freeloaded shifts.
 *
 * @return int
 */
function ShiftEntries_freeloaded_count()
{
    $result = DB::selectOne('SELECT COUNT(*) FROM `ShiftEntry` WHERE `freeloaded` = 1');

    if (empty($result)) {
        return 0;
    }

    return (int)array_shift($result);
}

/**
 * List users subscribed to a given shift.
 *
 * @param int $shift_id
 * @return array
 */
function ShiftEntries_by_shift($shift_id)
{
    return DB::select('
          SELECT
              `User`.`Nick`,
              `User`.`email`,
              `User`.`email_shiftinfo`,
              `User`.`Sprache`,
              `User`.`Gekommen`,
              `ShiftEntry`.`UID`,
              `ShiftEntry`.`TID`,
              `ShiftEntry`.`SID`,
              `AngelTypes`.`name` AS `angel_type_name`,
              `ShiftEntry`.`Comment`,
              `ShiftEntry`.`freeloaded`
          FROM `ShiftEntry`
          JOIN `User` ON `ShiftEntry`.`UID`=`User`.`UID`
          JOIN `AngelTypes` ON `ShiftEntry`.`TID`=`AngelTypes`.`id`
          WHERE `ShiftEntry`.`SID` = ?',
        [$shift_id]
    );
}

/**
 * Create a new shift entry.
 *
 * @param array $shift_entry
 * @return bool
 */
function ShiftEntry_create($shift_entry)
{
    $user = User($shift_entry['UID']);
    $shift = Shift($shift_entry['SID']);
    mail_shift_assign($user, $shift);
    $result = DB::insert('
          INSERT INTO `ShiftEntry` (
              `SID`,
              `TID`,
              `UID`,
              `Comment`,
              `freeload_comment`,
              `freeloaded`,
              `updated_microseconds`
          )
          VALUES(?, ?, ?, ?, ?, ?, ?)
        ',
        [
            $shift_entry['SID'],
            $shift_entry['TID'],
            $shift_entry['UID'],
            $shift_entry['Comment'],
            $shift_entry['freeload_comment'],
            (int)$shift_entry['freeloaded'],
            time_microseconds(),
        ]
    );
    engelsystem_log(
        'User ' . User_Nick_render($user)
        . ' signed up for shift ' . $shift['name']
        . ' from ' . date('Y-m-d H:i', $shift['start'])
        . ' to ' . date('Y-m-d H:i', $shift['end'])
    );

    return $result;
}

/**
 * Update a shift entry.
 *
 * @param array $shift_entry
 */
function ShiftEntry_update($shift_entry)
{
    DB::update('
      UPDATE `ShiftEntry`
      SET
          `Comment` = ?,
          `freeload_comment` = ?,
          `freeloaded` = ?,
          `updated_microseconds` = ?
      WHERE `id` = ?',
        [
            $shift_entry['Comment'],
            $shift_entry['freeload_comment'],
            (int)$shift_entry['freeloaded'],
            time_microseconds(),
            $shift_entry['id']
        ]
    );
}

/**
 * Get a shift entry.
 *
 * @param int $shift_entry_id
 * @return array|null
 */
function ShiftEntry($shift_entry_id)
{
    return DB::selectOne('SELECT * FROM `ShiftEntry` WHERE `id` = ?', [$shift_entry_id]);
}

/**
 * Delete a shift entry.
 *
 * @param array $shiftEntry
 */
function ShiftEntry_delete($shiftEntry)
{
    mail_shift_removed(User($shiftEntry['UID']), Shift($shiftEntry['SID']));
    DB::delete('DELETE FROM `ShiftEntry` WHERE `id` = ?', [$shiftEntry['id']]);

    $signout_user = User($shiftEntry['UID']);
    $shift = Shift($shiftEntry['SID']);
    $shifttype = ShiftType($shift['shifttype_id']);
    $room = Room($shift['RID']);
    $angeltype = AngelType($shiftEntry['TID']);

    engelsystem_log(
        'Shift signout: ' . User_Nick_render($signout_user) . ' from shift ' . $shifttype['name']
        . ' at ' . $room['Name']
        . ' from ' . date('Y-m-d H:i', $shift['start'])
        . ' to ' . date('Y-m-d H:i', $shift['end'])
        . ' as ' . $angeltype['name']
    );
}

/**
 * Returns next (or current) shifts of given user.
 *
 * @param array $user
 * @return array
 */
function ShiftEntries_upcoming_for_user($user)
{
    return DB::select('
        SELECT *
        FROM `ShiftEntry`
        JOIN `Shifts` ON (`Shifts`.`SID` = `ShiftEntry`.`SID`)
        JOIN `ShiftTypes` ON `ShiftTypes`.`id` = `Shifts`.`shifttype_id`
        WHERE `ShiftEntry`.`UID` = ?
        AND `Shifts`.`end` > ?
        ORDER BY `Shifts`.`end`
      ',
        [
            $user['UID'],
            time(),
        ]
    );
}

/**
 * Returns shifts completed by the given user.
 *
 * @param array $user
 * @return array
 */
function ShiftEntries_finished_by_user($user)
{
    return DB::select('
          SELECT *
          FROM `ShiftEntry`
          JOIN `Shifts` ON (`Shifts`.`SID` = `ShiftEntry`.`SID`)
          JOIN `ShiftTypes` ON `ShiftTypes`.`id` = `Shifts`.`shifttype_id`
          WHERE `ShiftEntry`.`UID` = ?
          AND `Shifts`.`end` < ?
          AND `ShiftEntry`.`freeloaded` = 0
          ORDER BY `Shifts`.`end`
      ',
        [
            $user['UID'],
            time(),
        ]
    );
}

/**
 * Returns all shift entries in given shift for given angeltype.
 *
 * @param int $shift_id
 * @param int $angeltype_id
 * @return array
 */
function ShiftEntries_by_shift_and_angeltype($shift_id, $angeltype_id)
{
    return DB::select('
            SELECT *
            FROM `ShiftEntry`
            WHERE `SID` = ?
            AND `TID` = ?
        ',
        [
            $shift_id,
            $angeltype_id,
        ]
    );
}

/**
 * Returns all freeloaded shifts for given user.
 *
 * @param array $user
 * @return array
 */
function ShiftEntries_freeloaded_by_user($user)
{
    return DB::select('
          SELECT *
          FROM `ShiftEntry`
          WHERE `freeloaded` = 1
          AND `UID` = ?
          ',
        [
            $user['UID']
        ]
    );
}
