<?php

use Engelsystem\Database\Db;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Room;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Engelsystem\ShiftsFilter;
use Engelsystem\ShiftSignupState;

/**
 * @param AngelType $angeltype
 * @return array
 */
function Shifts_by_angeltype(AngelType $angeltype)
{
    return Db::select('
        SELECT DISTINCT `Shifts`.* FROM `Shifts`
        JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`shift_id` = `Shifts`.`SID`
        LEFT JOIN schedule_shift AS s on Shifts.SID = s.shift_id
        WHERE `NeededAngelTypes`.`angel_type_id` = ?
        AND `NeededAngelTypes`.`count` > 0
        AND s.shift_id IS NULL

        UNION

        SELECT DISTINCT `Shifts`.* FROM `Shifts`
        JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`room_id` = `Shifts`.`RID`
        LEFT JOIN schedule_shift AS s on Shifts.SID = s.shift_id
        WHERE `NeededAngelTypes`.`angel_type_id` = ?
        AND `NeededAngelTypes`.`count` > 0
        AND NOT s.shift_id IS NULL
        ', [$angeltype->id, $angeltype->id]);
}

/**
 * Returns every shift with needed angels in the given time range.
 *
 * @param int               $start timestamp
 * @param int               $end timestamp
 * @param ShiftsFilter|null $filter
 *
 * @return array
 */
function Shifts_free($start, $end, ShiftsFilter $filter = null)
{
    $shifts = Db::select('
        SELECT * FROM (
            SELECT *
            FROM `Shifts`
            LEFT JOIN schedule_shift AS s on Shifts.SID = s.shift_id
            WHERE (`end` > ? AND `start` < ?)
            AND (SELECT SUM(`count`) FROM `NeededAngelTypes` WHERE `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`' . ($filter ? ' AND NeededAngelTypes.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . ')
            > (SELECT COUNT(*) FROM `ShiftEntry` WHERE `ShiftEntry`.`SID`=`Shifts`.`SID` AND `freeloaded`=0' . ($filter ? ' AND ShiftEntry.TID IN (' . implode(',', $filter->getTypes()) . ')' : '') . ')
            AND s.shift_id IS NULL
            ' . ($filter ? 'AND Shifts.RID IN (' . implode(',', $filter->getRooms()) . ')' : '') . '

            UNION

            SELECT *
            FROM `Shifts`
            LEFT JOIN schedule_shift AS s on Shifts.SID = s.shift_id
            WHERE (`end` > ? AND `start` < ?)
            AND (SELECT SUM(`count`) FROM `NeededAngelTypes` WHERE `NeededAngelTypes`.`room_id`=`Shifts`.`RID`' . ($filter ? ' AND NeededAngelTypes.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . ')
            > (SELECT COUNT(*) FROM `ShiftEntry` WHERE `ShiftEntry`.`SID`=`Shifts`.`SID` AND `freeloaded`=0' . ($filter ? ' AND ShiftEntry.TID IN (' . implode(',', $filter->getTypes()) . ')' : '') . ')
            AND NOT s.shift_id IS NULL
            ' . ($filter ? 'AND Shifts.RID IN (' . implode(',', $filter->getRooms()) . ')' : '') . '
        ) AS `tmp`
        ORDER BY `tmp`.`start`
        ', [
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
 * @param Room $room
 * @return array[]
 */
function Shifts_by_room(Room $room)
{
    return Db::select(
        'SELECT * FROM `Shifts` WHERE `RID`=? ORDER BY `start`',
        [$room->id]
    );
}

/**
 * @param ShiftsFilter $shiftsFilter
 * @return array[]
 */
function Shifts_by_ShiftsFilter(ShiftsFilter $shiftsFilter)
{
    $sql = '
    SELECT * FROM (
        SELECT DISTINCT `Shifts`.*, `shift_types`.`name`, `rooms`.`name` AS `room_name`
        FROM `Shifts`
        JOIN `rooms` ON `Shifts`.`RID` = `rooms`.`id`
        JOIN `shift_types` ON `shift_types`.`id` = `Shifts`.`shifttype_id`
        JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`shift_id` = `Shifts`.`SID`
        LEFT JOIN schedule_shift AS s on Shifts.SID = s.shift_id
        WHERE `Shifts`.`RID` IN (' . implode(',', $shiftsFilter->getRooms()) . ')
            AND `start` BETWEEN ? AND ?
            AND `NeededAngelTypes`.`angel_type_id` IN (' . implode(',', $shiftsFilter->getTypes()) . ')
            AND `NeededAngelTypes`.`count` > 0
            AND s.shift_id IS NULL

        UNION

        SELECT DISTINCT `Shifts`.*, `shift_types`.`name`, `rooms`.`name` AS `room_name`
        FROM `Shifts`
        JOIN `rooms` ON `Shifts`.`RID` = `rooms`.`id`
        JOIN `shift_types` ON `shift_types`.`id` = `Shifts`.`shifttype_id`
        JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`room_id`=`Shifts`.`RID`
        LEFT JOIN schedule_shift AS s on Shifts.SID = s.shift_id
        WHERE `Shifts`.`RID` IN (' . implode(',', $shiftsFilter->getRooms()) . ')
            AND `start` BETWEEN ? AND ?
            AND `NeededAngelTypes`.`angel_type_id` IN (' . implode(',', $shiftsFilter->getTypes()) . ')
            AND `NeededAngelTypes`.`count` > 0
            AND NOT s.shift_id IS NULL
    ) AS tmp_shifts

    ORDER BY `room_name`, `start`
    ';

    return Db::select(
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
            `angel_types`.`id`,
            `angel_types`.`name`,
            `angel_types`.`restricted`,
            `angel_types`.`no_self_signup`
        FROM `Shifts`
        JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`
        JOIN `angel_types` ON `angel_types`.`id`= `NeededAngelTypes`.`angel_type_id`
        LEFT JOIN schedule_shift AS s on Shifts.SID = s.shift_id
        WHERE `Shifts`.`RID` IN (' . implode(',', $shiftsFilter->getRooms()) . ')
        AND `start` BETWEEN ? AND ?
        AND s.shift_id IS NULL

        UNION

        SELECT
            `NeededAngelTypes`.*,
            `Shifts`.`SID`,
            `angel_types`.`id`,
            `angel_types`.`name`,
            `angel_types`.`restricted`,
            `angel_types`.`no_self_signup`
        FROM `Shifts`
        JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`room_id`=`Shifts`.`RID`
        JOIN `angel_types` ON `angel_types`.`id`= `NeededAngelTypes`.`angel_type_id`
        LEFT JOIN schedule_shift AS s on Shifts.SID = s.shift_id
        WHERE `Shifts`.`RID` IN (' . implode(',', $shiftsFilter->getRooms()) . ')
        AND `start` BETWEEN ? AND ?
        AND NOT s.shift_id IS NULL
    ';

    return Db::select(
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
 * @param array     $shift
 * @param AngelType $angeltype
 * @return array|null
 */
function NeededAngeltype_by_Shift_and_Angeltype($shift, AngelType $angeltype)
{
    return Db::selectOne(
        '
            SELECT
                `NeededAngelTypes`.*,
                `Shifts`.`SID`,
                `angel_types`.`id`,
                `angel_types`.`name`,
                `angel_types`.`restricted`,
                `angel_types`.`no_self_signup`
            FROM `Shifts`
            JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`shift_id`=`Shifts`.`SID`
            JOIN `angel_types` ON `angel_types`.`id`= `NeededAngelTypes`.`angel_type_id`
            LEFT JOIN schedule_shift AS s on Shifts.SID = s.shift_id
            WHERE `Shifts`.`SID`=?
            AND `angel_types`.`id`=?
            AND s.shift_id IS NULL

            UNION

            SELECT
                `NeededAngelTypes`.*,
                `Shifts`.`SID`,
                `angel_types`.`id`,
                `angel_types`.`name`,
                `angel_types`.`restricted`,
                `angel_types`.`no_self_signup`
            FROM `Shifts`
            JOIN `NeededAngelTypes` ON `NeededAngelTypes`.`room_id`=`Shifts`.`RID`
            JOIN `angel_types` ON `angel_types`.`id`= `NeededAngelTypes`.`angel_type_id`
            LEFT JOIN schedule_shift AS s on Shifts.SID = s.shift_id
            WHERE `Shifts`.`SID`=?
            AND `angel_types`.`id`=?
            AND NOT s.shift_id IS NULL
        ',
        [
            $shift['SID'],
            $angeltype->id,
            $shift['SID'],
            $angeltype->id
        ]
    );
}

/**
 * @param ShiftsFilter $shiftsFilter
 * @return array[]
 */
function ShiftEntries_by_ShiftsFilter(ShiftsFilter $shiftsFilter)
{
    $sql = sprintf(
        '
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
    return Db::select(
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
 * @param AngelType $needed_angeltype
 * @param array[]   $shift_entries
 * @return int
 */
function Shift_free_entries(AngelType $needed_angeltype, $shift_entries)
{
    $taken = 0;
    foreach ($shift_entries as $shift_entry) {
        if ($shift_entry['freeloaded'] == 0) {
            $taken++;
        }
    }

    $neededAngels = $needed_angeltype->count ?: 0;
    return max(0, $neededAngels - $taken);
}

/**
 * Check if shift signup is allowed from the end users point of view (no admin like privileges)
 *
 * @param User       $user
 * @param array      $shift The shift
 * @param AngelType  $angeltype The angeltype to which the user wants to sign up
 * @param array|null $user_angeltype
 * @param array|null $user_shifts List of the users shifts
 * @param AngelType  $needed_angeltype
 * @param array[]    $shift_entries
 * @return ShiftSignupState
 */
function Shift_signup_allowed_angel(
    $user,
    $shift,
    AngelType $angeltype,
    $user_angeltype,
    $user_shifts,
    AngelType $needed_angeltype,
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

    $shift_post_signup_total_allowed_seconds =
        (config('signup_post_fraction') * ($shift['end'] - $shift['start']))
        + (config('signup_post_minutes') * 60);

    if (time() > $shift['start'] + $shift_post_signup_total_allowed_seconds) {
        // you can only join if the shift is in future
        return new ShiftSignupState(ShiftSignupState::SHIFT_ENDED, $free_entries);
    }
    if ($free_entries == 0) {
        // you cannot join if shift is full
        return new ShiftSignupState(ShiftSignupState::OCCUPIED, $free_entries);
    }

    if (empty($user_angeltype)) {
        $user_angeltype = UserAngelType::whereUserId($user->id)->where('angel_type_id', $angeltype->id)->first();
    }

    if (
        empty($user_angeltype)
        || $angeltype->no_self_signup == 1
        || ($angeltype->restricted == 1 && !isset($user_angeltype['confirm_user_id']))
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
 * @param AngelType $needed_angeltype
 * @param array[]   $shift_entries
 * @return ShiftSignupState
 */
function Shift_signup_allowed_angeltype_supporter(AngelType $needed_angeltype, $shift_entries)
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
 * @param AngelType $needed_angeltype
 * @param array[]   $shift_entries
 * @return ShiftSignupState
 */
function Shift_signup_allowed_admin(AngelType $needed_angeltype, $shift_entries)
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
 * @param array     $shift The shift
 * @param AngelType $angeltype The angeltype
 * @param int       $signout_user_id The user that was signed up for the shift
 * @return bool
 */
function Shift_signout_allowed($shift, AngelType $angeltype, $signout_user_id)
{
    $user = auth()->user();

    // user shifts admin can sign out any user at any time
    if (auth()->can('user_shifts_admin')) {
        return true;
    }

    // angeltype supporter can sign out any user at any time from their supported angeltype
    if (
        auth()->can('shiftentry_edit_angeltype_supporter')
        && ($user->isAngelTypeSupporter($angeltype) || auth()->can('admin_user_angeltypes'))
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
 * @param array      $shift The shift
 * @param AngelType  $angeltype The angeltype to which the user wants to sign up
 * @param array|null $user_angeltype
 * @param array|null $user_shifts List of the users shifts
 * @param AngelType  $needed_angeltype
 * @param array[]    $shift_entries
 * @return ShiftSignupState
 */
function Shift_signup_allowed(
    $signup_user,
    $shift,
    AngelType $angeltype,
    $user_angeltype,
    $user_shifts,
    AngelType $needed_angeltype,
    $shift_entries
) {
    if (auth()->can('user_shifts_admin')) {
        return Shift_signup_allowed_admin($needed_angeltype, $shift_entries);
    }

    if (
        auth()->can('shiftentry_edit_angeltype_supporter')
        && (auth()->user()->isAngelTypeSupporter($angeltype) || auth()->can('admin_user_angeltypes'))
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
 * Delete a shift.
 *
 * @param int $shift_id
 */
function Shift_delete($shift_id)
{
    Db::delete('DELETE FROM `Shifts` WHERE `SID`=?', [$shift_id]);
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
    $shift['name'] = ShiftType::find($shift['shifttype_id'])->name;
    mail_shift_change(Shift($shift['SID']), $shift);

    return Db::update(
        '
        UPDATE `Shifts` SET
        `shifttype_id` = ?,
        `start` = ?,
        `end` = ?,
        `RID` = ?,
        `title` = ?,
        `description` = ?,
        `URL` = ?,
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
            $shift['description'],
            $shift['URL'],
            $user->id,
            time(),
            $shift['SID']
        ]
    );
}

/**
 * Create a new shift.
 *
 * @param array $shift
 * @param int   $transactionId
 * @return int|false ID of the new created shift
 */
function Shift_create($shift, $transactionId = null)
{
    Db::insert(
        '
        INSERT INTO `Shifts` (
            `shifttype_id`,
            `start`,
            `end`,
            `RID`,
            `title`,
            `description`,
            `URL`,
            `transaction_id`,
            `created_by_user_id`,
            `edited_at_timestamp`,
            `created_at_timestamp`
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ',
        [
            $shift['shifttype_id'],
            $shift['start'],
            $shift['end'],
            $shift['RID'],
            $shift['title'],
            $shift['description'],
            $shift['URL'],
            $transactionId,
            auth()->user()->id,
            time(),
            time(),
        ]
    );

    return Db::getPdo()->lastInsertId();
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
    return Db::select(
        '
        SELECT
            `rooms`.*,
            `rooms`.name AS Name,
            `shift_types`.`id` AS `shifttype_id`,
            `shift_types`.`name`,
            `ShiftEntry`.`id`,
            `ShiftEntry`.`SID`,
            `ShiftEntry`.`TID`,
            `ShiftEntry`.`UID`,
            `ShiftEntry`.`freeloaded`,
            `ShiftEntry`.`Comment`,
            ' . ($include_freeload_comments ? '`ShiftEntry`.`freeload_comment`, ' : '') . '
            `Shifts`.*,
            @@session.time_zone AS timezone,
            ? AS event_timezone
        FROM `ShiftEntry`
        JOIN `Shifts` ON (`ShiftEntry`.`SID` = `Shifts`.`SID`)
        JOIN `shift_types` ON (`shift_types`.`id` = `Shifts`.`shifttype_id`)
        JOIN `rooms` ON (`Shifts`.`RID` = `rooms`.`id`)
        WHERE `UID` = ?
        ORDER BY `start`
        ',
        [
            config('timezone'),
            $userId,
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
    $result = Db::selectOne('
        SELECT `Shifts`.*, `shift_types`.`name`
        FROM `Shifts`
        JOIN `shift_types` ON (`shift_types`.`id` = `Shifts`.`shifttype_id`)
        WHERE `SID`=?', [$shift_id]);

    if (empty($result)) {
        return null;
    }

    $shiftsEntry_source = Db::select('
        SELECT
            `ShiftEntry`.`id`, `ShiftEntry`.`TID` , `ShiftEntry`.`UID` , `ShiftEntry`.`freeloaded`,
            `users`.`name` AS `username`, `users`.`id` AS `user_id`
        FROM `ShiftEntry`
        LEFT JOIN `users` ON (`users`.`id` = `ShiftEntry`.`UID`)
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
