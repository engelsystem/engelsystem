<?php

use Engelsystem\Database\Db;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\Shifts\ShiftSignupStatus;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Engelsystem\ShiftsFilter;
use Engelsystem\ShiftSignupState;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

/**
 * @param AngelType $angeltype
 * @return array
 */
function Shifts_by_angeltype(AngelType $angeltype)
{
    return Db::select('
        SELECT DISTINCT `shifts`.* FROM `shifts`
        JOIN `needed_angel_types` ON `needed_angel_types`.`shift_id` = `shifts`.`id`
        LEFT JOIN schedule_shift AS s on shifts.id = s.shift_id
        WHERE `needed_angel_types`.`angel_type_id` = ?
        AND s.shift_id IS NULL

        UNION

        SELECT DISTINCT `shifts`.* FROM `shifts`
        JOIN `needed_angel_types` ON `needed_angel_types`.`room_id` = `shifts`.`room_id`
        LEFT JOIN schedule_shift AS s on shifts.id = s.shift_id
        WHERE `needed_angel_types`.`angel_type_id` = ?
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
 * @return Collection|Shift[]
 */
function Shifts_free($start, $end, ShiftsFilter $filter = null)
{
    $start = Carbon::createFromTimestamp($start);
    $end = Carbon::createFromTimestamp($end);

    $shifts = Db::select('
        SELECT *
        FROM (
            SELECT id, start
            FROM `shifts`
            LEFT JOIN schedule_shift AS s on shifts.id = s.shift_id
            WHERE (`end` > ? AND `start` < ?)
            AND (SELECT SUM(`count`) FROM `needed_angel_types` WHERE `needed_angel_types`.`shift_id`=`shifts`.`id`' . ($filter ? ' AND needed_angel_types.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . ')
            > (SELECT COUNT(*) FROM `shift_entries` WHERE `shift_entries`.`shift_id`=`shifts`.`id` AND shift_entries.`freeloaded`=0' . ($filter ? ' AND shift_entries.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . ')
            AND s.shift_id IS NULL
            ' . ($filter ? 'AND shifts.room_id IN (' . implode(',', $filter->getRooms()) . ')' : '') . '

            UNION

            SELECT id, start
            FROM `shifts`
            LEFT JOIN schedule_shift AS s on shifts.id = s.shift_id
            WHERE (`end` > ? AND `start` < ?)
            AND (SELECT SUM(`count`) FROM `needed_angel_types` WHERE `needed_angel_types`.`room_id`=`shifts`.`room_id`' . ($filter ? ' AND needed_angel_types.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . ')
            > (SELECT COUNT(*) FROM `shift_entries` WHERE `shift_entries`.`shift_id`=`shifts`.`id` AND `freeloaded`=0' . ($filter ? ' AND shift_entries.angel_type_id IN (' . implode(',', $filter->getTypes()) . ')' : '') . ')
            AND NOT s.shift_id IS NULL
            ' . ($filter ? 'AND shifts.room_id IN (' . implode(',', $filter->getRooms()) . ')' : '') . '
        ) AS `tmp`
        ORDER BY `tmp`.`start`
        ', [
        $start,
        $end,
        $start,
        $end,
    ]);

    $shifts = collect($shifts);

    return Shift::query()
        ->whereIn('id', $shifts->pluck('id')->toArray())
        ->get();
}

/**
 * @param ShiftsFilter $shiftsFilter
 * @return Shift[]|Collection
 */
function Shifts_by_ShiftsFilter(ShiftsFilter $shiftsFilter)
{
    $sql = '
    SELECT * FROM (
        SELECT DISTINCT `shifts`.*, `shift_types`.`name`, `rooms`.`name` AS `room_name`
        FROM `shifts`
        JOIN `rooms` ON `shifts`.`room_id` = `rooms`.`id`
        JOIN `shift_types` ON `shift_types`.`id` = `shifts`.`shift_type_id`
        JOIN `needed_angel_types` ON `needed_angel_types`.`shift_id` = `shifts`.`id`
        LEFT JOIN schedule_shift AS s on shifts.id = s.shift_id
        WHERE `shifts`.`room_id` IN (' . implode(',', $shiftsFilter->getRooms()) . ')
            AND `start` BETWEEN ? AND ?
            AND `needed_angel_types`.`angel_type_id` IN (' . implode(',', $shiftsFilter->getTypes()) . ')
            AND s.shift_id IS NULL

        UNION

        SELECT DISTINCT `shifts`.*, `shift_types`.`name`, `rooms`.`name` AS `room_name`
        FROM `shifts`
        JOIN `rooms` ON `shifts`.`room_id` = `rooms`.`id`
        JOIN `shift_types` ON `shift_types`.`id` = `shifts`.`shift_type_id`
        JOIN `needed_angel_types` ON `needed_angel_types`.`room_id`=`shifts`.`room_id`
        LEFT JOIN schedule_shift AS s on shifts.id = s.shift_id
        WHERE `shifts`.`room_id` IN (' . implode(',', $shiftsFilter->getRooms()) . ')
            AND `start` BETWEEN ? AND ?
            AND `needed_angel_types`.`angel_type_id` IN (' . implode(',', $shiftsFilter->getTypes()) . ')
            AND NOT s.shift_id IS NULL
    ) AS tmp_shifts

    ORDER BY `room_name`, `start`
    ';

    $shiftsData = Db::select(
        $sql,
        [
            $shiftsFilter->getStart(),
            $shiftsFilter->getEnd(),
            $shiftsFilter->getStart(),
            $shiftsFilter->getEnd(),
        ]
    );

    $shifts = [];
    foreach ($shiftsData as $shift) {
        $shifts[] = (new Shift())->forceFill($shift);
    }

    return collect($shifts);
}

/**
 * @param ShiftsFilter $shiftsFilter
 * @return array[]
 */
function NeededAngeltypes_by_ShiftsFilter(ShiftsFilter $shiftsFilter)
{
    $sql = '
        SELECT
            `needed_angel_types`.*,
            `shifts`.`id` AS shift_id,
            `angel_types`.`id`,
            `angel_types`.`name`,
            `angel_types`.`restricted`,
            `angel_types`.`no_self_signup`
        FROM `shifts`
        JOIN `needed_angel_types` ON `needed_angel_types`.`shift_id`=`shifts`.`id`
        JOIN `angel_types` ON `angel_types`.`id`= `needed_angel_types`.`angel_type_id`
        LEFT JOIN schedule_shift AS s on shifts.id = s.shift_id
        WHERE `shifts`.`room_id` IN (' . implode(',', $shiftsFilter->getRooms()) . ')
        AND shifts.`start` BETWEEN ? AND ?
        AND s.shift_id IS NULL

        UNION

        SELECT
            `needed_angel_types`.*,
            `shifts`.`id` AS shift_id,
            `angel_types`.`id`,
            `angel_types`.`name`,
            `angel_types`.`restricted`,
            `angel_types`.`no_self_signup`
        FROM `shifts`
        JOIN `needed_angel_types` ON `needed_angel_types`.`room_id`=`shifts`.`room_id`
        JOIN `angel_types` ON `angel_types`.`id`= `needed_angel_types`.`angel_type_id`
        LEFT JOIN schedule_shift AS s on shifts.id = s.shift_id
        WHERE `shifts`.`room_id` IN (' . implode(',', $shiftsFilter->getRooms()) . ')
        AND shifts.`start` BETWEEN ? AND ?
        AND NOT s.shift_id IS NULL
    ';

    return Db::select(
        $sql,
        [
            $shiftsFilter->getStart(),
            $shiftsFilter->getEnd(),
            $shiftsFilter->getStart(),
            $shiftsFilter->getEnd(),
        ]
    );
}

/**
 * @param Shift     $shift
 * @param AngelType $angeltype
 * @return array|null
 */
function NeededAngeltype_by_Shift_and_Angeltype(Shift $shift, AngelType $angeltype)
{
    return Db::selectOne(
        '
            SELECT
                `needed_angel_types`.*,
                `shifts`.`id` AS shift_id,
                `angel_types`.`id`,
                `angel_types`.`name`,
                `angel_types`.`restricted`,
                `angel_types`.`no_self_signup`
            FROM `shifts`
            JOIN `needed_angel_types` ON `needed_angel_types`.`shift_id`=`shifts`.`id`
            JOIN `angel_types` ON `angel_types`.`id`= `needed_angel_types`.`angel_type_id`
            LEFT JOIN schedule_shift AS s on shifts.id = s.shift_id
            WHERE `shifts`.`id`=?
            AND `angel_types`.`id`=?
            AND s.shift_id IS NULL

            UNION

            SELECT
                `needed_angel_types`.*,
                `shifts`.`id` AS shift_id,
                `angel_types`.`id`,
                `angel_types`.`name`,
                `angel_types`.`restricted`,
                `angel_types`.`no_self_signup`
            FROM `shifts`
            JOIN `needed_angel_types` ON `needed_angel_types`.`room_id`=`shifts`.`room_id`
            JOIN `angel_types` ON `angel_types`.`id`= `needed_angel_types`.`angel_type_id`
            LEFT JOIN schedule_shift AS s on shifts.id = s.shift_id
            WHERE `shifts`.`id`=?
            AND `angel_types`.`id`=?
            AND NOT s.shift_id IS NULL
        ',
        [
            $shift->id,
            $angeltype->id,
            $shift->id,
            $angeltype->id,
        ]
    );
}

/**
 * @param ShiftsFilter $shiftsFilter
 * @return ShiftEntry[]|Collection
 */
function ShiftEntries_by_ShiftsFilter(ShiftsFilter $shiftsFilter)
{
    return ShiftEntry::with('user')
        ->join('shifts', 'shifts.id', 'shift_entries.shift_id')
        ->whereIn('shifts.room_id', $shiftsFilter->getRooms())
        ->whereBetween('start', [$shiftsFilter->getStart(), $shiftsFilter->getEnd()])
        ->get();
}

/**
 * Check if a shift collides with other shifts (in time).
 *
 * @param Shift              $shift
 * @param Shift[]|Collection $shifts
 * @return bool
 */
function Shift_collides(Shift $shift, $shifts)
{
    foreach ($shifts as $other_shift) {
        if ($shift->id != $other_shift->id) {
            if (
                !(
                    $shift->start->timestamp >= $other_shift->end->timestamp
                    || $shift->end->timestamp <= $other_shift->start->timestamp
                )
            ) {
                return true;
            }
        }
    }
    return false;
}

/**
 * Returns the number of needed angels/free shift entries for an angeltype.
 *
 * @param AngelType               $needed_angeltype
 * @param ShiftEntry[]|Collection $shift_entries
 * @return int
 */
function Shift_free_entries(AngelType $needed_angeltype, $shift_entries)
{
    $taken = 0;
    foreach ($shift_entries as $shift_entry) {
        if (!$shift_entry->freeloaded) {
            $taken++;
        }
    }

    $neededAngels = $needed_angeltype->count ?: 0;
    return max(0, $neededAngels - $taken);
}

/**
 * Check if shift signup is allowed from the end users point of view (no admin like privileges)
 *
 * @param User                    $user
 * @param Shift                   $shift The shift
 * @param AngelType               $angeltype The angeltype to which the user wants to sign up
 * @param array|null              $user_angeltype
 * @param Shift[]|Collection|null $user_shifts List of the users shifts
 * @param AngelType               $needed_angeltype
 * @param ShiftEntry[]|Collection $shift_entries
 * @return ShiftSignupState
 */
function Shift_signup_allowed_angel(
    $user,
    Shift $shift,
    AngelType $angeltype,
    $user_angeltype,
    $user_shifts,
    AngelType $needed_angeltype,
    $shift_entries
) {
    $free_entries = Shift_free_entries($needed_angeltype, $shift_entries);

    if (config('signup_requires_arrival') && !$user->state->arrived) {
        return new ShiftSignupState(ShiftSignupStatus::NOT_ARRIVED, $free_entries);
    }

    if (config('signup_advance_hours') && $shift->start->timestamp > time() + config('signup_advance_hours') * 3600) {
        return new ShiftSignupState(ShiftSignupStatus::NOT_YET, $free_entries);
    }

    if (is_null($user_shifts) || $user_shifts->isEmpty()) {
        $user_shifts = Shifts_by_user($user->id);
    }

    $signed_up = false;
    foreach ($user_shifts as $user_shift) {
        if ($user_shift->id == $shift->id) {
            $signed_up = true;
            break;
        }
    }

    if ($signed_up) {
        // you cannot join if you already signed up for this shift
        return new ShiftSignupState(ShiftSignupStatus::SIGNED_UP, $free_entries);
    }

    $shift_post_signup_total_allowed_seconds =
        (config('signup_post_fraction') * ($shift->end->timestamp - $shift->start->timestamp))
        + (config('signup_post_minutes') * 60);

    if (time() > $shift->start->timestamp + $shift_post_signup_total_allowed_seconds) {
        // you can only join if the shift is in future
        return new ShiftSignupState(ShiftSignupStatus::SHIFT_ENDED, $free_entries);
    }
    if ($free_entries == 0) {
        // you cannot join if shift is full
        return new ShiftSignupState(ShiftSignupStatus::OCCUPIED, $free_entries);
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

        return new ShiftSignupState(ShiftSignupStatus::ANGELTYPE, $free_entries);
    }

    if (Shift_collides($shift, $user_shifts)) {
        // you cannot join if user already joined a parallel of this shift
        return new ShiftSignupState(ShiftSignupStatus::COLLIDES, $free_entries);
    }

    // Hooray, shift is free for you!
    return new ShiftSignupState(ShiftSignupStatus::FREE, $free_entries);
}

/**
 * Check if an angeltype supporter can sign up a user to a shift.
 *
 * @param AngelType               $needed_angeltype
 * @param ShiftEntry[]|Collection $shift_entries
 * @return ShiftSignupState
 */
function Shift_signup_allowed_angeltype_supporter(AngelType $needed_angeltype, $shift_entries)
{
    $free_entries = Shift_free_entries($needed_angeltype, $shift_entries);
    if ($free_entries == 0) {
        return new ShiftSignupState(ShiftSignupStatus::OCCUPIED, $free_entries);
    }

    return new ShiftSignupState(ShiftSignupStatus::FREE, $free_entries);
}

/**
 * Check if an admin can sign up a user to a shift.
 *
 * @param AngelType               $needed_angeltype
 * @param ShiftEntry[]|Collection $shift_entries
 * @return ShiftSignupState
 */
function Shift_signup_allowed_admin(AngelType $needed_angeltype, $shift_entries)
{
    $free_entries = Shift_free_entries($needed_angeltype, $shift_entries);

    if ($free_entries == 0) {
        // User shift admins may join anybody in every shift
        return new ShiftSignupState(ShiftSignupStatus::ADMIN, $free_entries);
    }

    return new ShiftSignupState(ShiftSignupStatus::FREE, $free_entries);
}

/**
 * Check if an angel can sign out from a shift.
 *
 * @param Shift     $shift The shift
 * @param AngelType $angeltype The angeltype
 * @param int       $signout_user_id The user that was signed up for the shift
 * @return bool
 */
function Shift_signout_allowed(Shift $shift, AngelType $angeltype, $signout_user_id)
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

    if ($signout_user_id == $user->id && $shift->start->timestamp > time() + config('last_unsubscribe') * 3600) {
        return true;
    }

    return false;
}

/**
 * Check if an angel can sign up for given shift.
 *
 * @param User                    $signup_user
 * @param Shift                   $shift The shift
 * @param AngelType               $angeltype The angeltype to which the user wants to sign up
 * @param array|null              $user_angeltype
 * @param Shift[]|Collection|null $user_shifts List of the users shifts
 * @param AngelType               $needed_angeltype
 * @param ShiftEntry[]|Collection $shift_entries
 * @return ShiftSignupState
 */
function Shift_signup_allowed(
    $signup_user,
    Shift $shift,
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
 * Return users shifts.
 *
 * @param int  $userId
 * @param bool $include_freeloaded_comments
 * @return SupportCollection|Shift[]
 */
function Shifts_by_user($userId, $include_freeloaded_comments = false)
{
    $shiftsData = Db::select(
        '
        SELECT
            `rooms`.*,
            `rooms`.name AS Name,
            `shift_types`.`id` AS `shifttype_id`,
            `shift_types`.`name`,
            `shift_entries`.`id` as shift_entry_id,
            `shift_entries`.`shift_id`,
            `shift_entries`.`angel_type_id`,
            `shift_entries`.`user_id`,
            `shift_entries`.`freeloaded`,
            `shift_entries`.`user_comment`,
            ' . ($include_freeloaded_comments ? '`shift_entries`.`freeloaded_comment`, ' : '') . '
            `shifts`.*
        FROM `shift_entries`
        JOIN `shifts` ON (`shift_entries`.`shift_id` = `shifts`.`id`)
        JOIN `shift_types` ON (`shift_types`.`id` = `shifts`.`shift_type_id`)
        JOIN `rooms` ON (`shifts`.`room_id` = `rooms`.`id`)
        WHERE shift_entries.`user_id` = ?
        ORDER BY `start`
        ',
        [
            $userId,
        ]
    );

    $shifts = [];
    foreach ($shiftsData as $data) {
        $shifts[] = (new Shift())->forceFill($data);
    }

    return collect($shifts);
}

/**
 * Returns Shift by id or extends existing Shift
 *
 * @param int|Shift $shift Shift ID or shift model
 * @return Shift|null
 */
function Shift($shift)
{
    if (!$shift instanceof Shift) {
        $shift = Shift::find($shift);
    }

    if (!$shift) {
        return null;
    }

    $neededAngels = [];
    $angelTypes = NeededAngelTypes_by_shift($shift->id);
    foreach ($angelTypes as $type) {
        $neededAngels[] = [
            'angel_type_id' => $type['angel_type_id'],
            'count'         => $type['count'],
            'restricted'    => $type['restricted'],
            'taken'         => $type['taken'],
        ];
    }
    $shift->neededAngels = $neededAngels;

    return $shift;
}
