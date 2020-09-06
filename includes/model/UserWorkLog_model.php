<?php

use Carbon\Carbon;
use Engelsystem\Database\Db;
use Engelsystem\Models\Room;
use Engelsystem\Models\User\User;

/**
 * Load a single work log entry.
 *
 * @param int $user_worklog_id
 * @return array|null
 */
function UserWorkLog($user_worklog_id)
{
    $workLog = Db::selectOne("SELECT * FROM `UserWorkLog` WHERE `id`=?", [
        $user_worklog_id
    ]);

    return empty($workLog) ? null : $workLog;
}

/**
 * Returns all work log entries for a user.
 *
 * @param int         $userId
 * @param Carbon|null $sinceTime
 * @return array[]
 */
function UserWorkLogsForUser($userId, Carbon $sinceTime = null)
{
    return Db::select(
        '
            SELECT *
            FROM `UserWorkLog`
            WHERE `user_id`=?
            ' . ($sinceTime ? 'AND work_timestamp >= ' . $sinceTime->getTimestamp() : '') . '
            ORDER BY `created_timestamp`
        ',
        [
            $userId
        ]
    );
}

/**
 * Delete a work log entry.
 *
 * @param $userWorkLog
 * @return int
 */
function UserWorkLog_delete($userWorkLog)
{
    $user_source = User::find($userWorkLog['user_id']);
    $result = Db::delete("DELETE FROM `UserWorkLog` WHERE `id`=?", [
        $userWorkLog['id']
    ]);

    engelsystem_log(sprintf(
        'Delete work log for %s, %s hours, %s',
        User_Nick_render($user_source, true),
        $userWorkLog['work_hours'],
        $userWorkLog['comment']
    ));

    return $result;
}

/**
 * Update work log entry (only work hours and comment)
 *
 * @param $userWorkLog
 * @return int
 */
function UserWorkLog_update($userWorkLog)
{
    $user_source = User::find($userWorkLog['user_id']);

    $result = Db::update("UPDATE `UserWorkLog` SET
        `work_timestamp`=?,
        `work_hours`=?,
        `comment`=?
        WHERE `id`=?", [
        $userWorkLog['work_timestamp'],
        $userWorkLog['work_hours'],
        $userWorkLog['comment'],
        $userWorkLog['id']
    ]);

    engelsystem_log(sprintf(
            'Updated work log for %s, %s hours, %s',
            User_Nick_render($user_source, true),
            $userWorkLog['work_hours'],
            $userWorkLog['comment'])
    );

    return $result;
}

/**
 * Create a new work log entry
 *
 * @param $userWorkLog
 * @return bool
 */
function UserWorkLog_create($userWorkLog)
{
    $user = auth()->user();

    $user_source = User::find($userWorkLog['user_id']);

    $result = Db::insert("INSERT INTO `UserWorkLog` (
            `user_id`,
            `work_timestamp`,
            `work_hours`,
            `comment`,
            `created_user_id`,
            `created_timestamp`
        )
        VALUES (?, ?, ?, ?, ?, ?)", [
        $userWorkLog['user_id'],
        $userWorkLog['work_timestamp'],
        $userWorkLog['work_hours'],
        $userWorkLog['comment'],
        $user->id,
        time()
    ]);

    engelsystem_log(sprintf('Added work log entry for %s, %s hours, %s', User_Nick_render($user_source, true),
        $userWorkLog['work_hours'], $userWorkLog['comment']));

    return $result;
}

/**
 * @param array|int $shift
 */
function UserWorkLog_from_shift($shift)
{
    $shift = is_array($shift) ? $shift : Shift($shift);
    $nightShifts = config('night_shifts');

    if ($shift['start'] > time()) {
        return;
    }

    $room = Room::find($shift['RID']);
    foreach ($shift['ShiftEntry'] as $entry) {
        if ($entry['freeloaded']) {
            continue;
        }

        $type = AngelType($entry['TID']);

        $nightShiftMultiplier = 1;
        $shiftStart = Carbon::createFromTimestamp($shift['start']);
        $shiftEnd = Carbon::createFromTimestamp($shift['end']);
        if (
            $nightShifts['enabled']
            && (
                $shiftStart->hour >= $nightShifts['start'] && $shiftStart->hour < $nightShifts['end']
                || $shiftEnd->hour >= $nightShifts['start'] && $shiftEnd->hour < $nightShifts['end']
            )
        ) {
            $nightShiftMultiplier = $nightShifts['multiplier'];
        }

        UserWorkLog_create([
            'user_id'        => $entry['UID'],
            'work_timestamp' => $shift['start'],
            'work_hours'     => (($shift['end'] - $shift['start']) / 60 / 60) * $nightShiftMultiplier,
            'comment'        => sprintf(
                '%s (%s as %s) in %s, %s-%s',
                $shift['name'],
                $shift['title'],
                $type['name'],
                $room->name,
                Carbon::createFromTimestamp($shift['start'])->format(__('m/d/Y h:i a')),
                Carbon::createFromTimestamp($shift['end'])->format(__('m/d/Y h:i a'))
            ),
        ]);
    }
}

/**
 * New user work log entry
 *
 * @param int $userId
 * @return array
 */
function UserWorkLog_new($userId)
{
    $work_date = parse_date('Y-m-d H:i', date('Y-m-d 00:00', time()));

    /** @var Carbon $buildup */
    $buildup = config('buildup_start');
    if (!empty($buildup)) {
        $work_date = $buildup->format('Y-m-d H:i');
    }

    return [
        'user_id'        => $userId,
        'work_timestamp' => $work_date,
        'work_hours'     => 0,
        'comment'        => ''
    ];
}
