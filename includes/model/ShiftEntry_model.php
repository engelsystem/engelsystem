<?php

use Carbon\Carbon;
use Engelsystem\Database\Db;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User;

/**
 * Counts all freeloaded shifts.
 *
 * @return int
 */
function ShiftEntries_freeloaded_count()
{
    $result = Db::selectOne('SELECT COUNT(*) FROM `ShiftEntry` WHERE `freeloaded` = 1');

    if (empty($result)) {
        return 0;
    }

    return (int) array_shift($result);
}

/**
 * List users subscribed to a given shift.
 *
 * @param int $shift_id
 * @return array
 */
function ShiftEntries_by_shift($shift_id)
{
    return Db::select(
        '
            SELECT
                `users`.*,
                `ShiftEntry`.`UID`,
                `ShiftEntry`.`TID`,
                `ShiftEntry`.`SID`,
                `angel_types`.`name` AS `angel_type_name`,
                `ShiftEntry`.`Comment`,
                `ShiftEntry`.`freeloaded`
            FROM `ShiftEntry`
            JOIN `users` ON `ShiftEntry`.`UID`=`users`.`id`
            JOIN `angel_types` ON `ShiftEntry`.`TID`=`angel_types`.`id`
            WHERE `ShiftEntry`.`SID` = ?
        ',
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
    $user = User::find($shift_entry['UID']);
    $shift = Shift($shift_entry['SID']);
    $shifttype = $shift->shiftType;
    $room = $shift->room;
    $angeltype = AngelType::find($shift_entry['TID']);
    $result = Db::insert(
        '
            INSERT INTO `ShiftEntry` (
                `SID`,
                `TID`,
                `UID`,
                `Comment`,
                `freeload_comment`,
                `freeloaded`
            )
            VALUES(?, ?, ?, ?, ?, ?)
        ',
        [
            $shift_entry['SID'],
            $shift_entry['TID'],
            $shift_entry['UID'],
            $shift_entry['Comment'],
            $shift_entry['freeload_comment'],
            (int) $shift_entry['freeloaded'],
        ]
    );
    engelsystem_log(
        'User ' . User_Nick_render($user, true)
        . ' signed up for shift ' . $shift->title
        . ' (' . $shifttype->name . ')'
        . ' at ' . $room->name
        . ' from ' . $shift->start->format('Y-m-d H:i')
        . ' to ' . $shift->end->format('Y-m-d H:i')
        . ' as ' . $angeltype->name
    );
    mail_shift_assign($user, $shift);

    return $result;
}

/**
 * Update a shift entry.
 *
 * @param array $shift_entry
 */
function ShiftEntry_update($shift_entry)
{
    Db::update(
        '
            UPDATE `ShiftEntry`
            SET
                `Comment` = ?,
                `freeload_comment` = ?,
                `freeloaded` = ?
            WHERE `id` = ?
        ',
        [
            $shift_entry['Comment'],
            $shift_entry['freeload_comment'],
            (int) $shift_entry['freeloaded'],
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
    $shiftEntry = Db::selectOne('SELECT * FROM `ShiftEntry` WHERE `id` = ?', [$shift_entry_id]);

    return empty($shiftEntry) ? null : $shiftEntry;
}

/**
 * Delete a shift entry.
 *
 * @param array $shiftEntry
 */
function ShiftEntry_delete($shiftEntry)
{
    Db::delete('DELETE FROM `ShiftEntry` WHERE `id` = ?', [$shiftEntry['id']]);

    $signout_user = User::find($shiftEntry['UID']);
    $shift = Shift($shiftEntry['SID']);
    $shifttype = $shift->shiftType;
    $room = $shift->room;
    $angeltype = AngelType::find($shiftEntry['TID']);

    engelsystem_log(
        'Shift signout: ' . User_Nick_render($signout_user, true)
        . ' from shift ' . $shift->title
        . ' (' . $shifttype->name . ')'
        . ' at ' . $room->name
        . ' from ' . $shift->start->format('Y-m-d H:i')
        . ' to ' . $shift->end->format('Y-m-d H:i')
        . ' as ' . $angeltype->name
    );

    mail_shift_removed(User::find($shiftEntry['UID']), $shift);
}

/**
 * Returns next (or current) shifts of given user.
 *
 * @param User $user
 * @return array
 */
function ShiftEntries_upcoming_for_user(User $user)
{
    return Db::select(
        '
        SELECT *, shifts.id as shift_id
        FROM `ShiftEntry`
        JOIN `shifts` ON (`shifts`.`id` = `ShiftEntry`.`SID`)
        JOIN `shift_types` ON `shift_types`.`id` = `shifts`.`shift_type_id`
        WHERE `ShiftEntry`.`UID` = ?
        AND `shifts`.`end` > NOW()
        ORDER BY `shifts`.`end`
        ',
        [
            $user->id
        ]
    );
}

/**
 * Returns shifts completed by the given user.
 *
 * @param User $user
 * @param Carbon|null $sinceTime
 * @return array
 */
function ShiftEntries_finished_by_user(User $user, Carbon $sinceTime = null)
{
    return Db::select(
        '
            SELECT *
            FROM `ShiftEntry`
            JOIN `shifts` ON (`shifts`.`id` = `ShiftEntry`.`SID`)
            JOIN `shift_types` ON `shift_types`.`id` = `shifts`.`shift_type_id`
            WHERE `ShiftEntry`.`UID` = ?
            AND `shifts`.`end` < NOW()
            AND `ShiftEntry`.`freeloaded` = 0
            ' . ($sinceTime ? 'AND shifts.start >= "' . $sinceTime->toString() . '"' : '') . '
            ORDER BY `shifts`.`end` desc
        ',
        [
            $user->id,
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
    return Db::select(
        '
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
 * @param int $userId
 * @return array
 */
function ShiftEntries_freeloaded_by_user($userId)
{
    return Db::select(
        '
            SELECT *
            FROM `ShiftEntry`
            WHERE `freeloaded` = 1
            AND `UID` = ?
        ',
        [
            $userId
        ]
    );
}
