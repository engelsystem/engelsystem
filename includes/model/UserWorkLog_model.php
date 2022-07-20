<?php

use Carbon\Carbon;
use Engelsystem\Models\Room;
use Engelsystem\Models\Worklog;
use Illuminate\Support\Collection;

/**
 * Returns all work log entries for a user.
 *
 * @param int         $userId
 * @param Carbon|null $sinceTime
 *
 * @return Worklog[]|Collection
 */
function UserWorkLogsForUser($userId, Carbon $sinceTime = null)
{
    $worklogs = Worklog::whereUserId($userId);
    if ($sinceTime) {
        $worklogs = $worklogs->whereDate('worked_at', '>=', $sinceTime);
    }

    return $worklogs->get();
}

/**
 * Delete a work log entry.
 *
 * @param Worklog $worklog
 * @return int
 */
function UserWorkLog_delete(Worklog $worklog)
{
    $result = $worklog->delete();

    engelsystem_log(sprintf(
        'Delete work log for %s, %s hours, %s',
        User_Nick_render($worklog->user, true),
        $worklog->hours,
        $worklog->comment
    ));

    return $result;
}

/**
 * Create a new work log entry
 *
 * @param Worklog $worklog
 * @return bool
 */
function UserWorkLog_create(Worklog $worklog)
{
    $user = auth()->user();
    $worklog->creator()->associate($user);
    $result = $worklog->save();

    engelsystem_log(sprintf(
        'Added work log entry for %s, %s hours, %s',
        User_Nick_render($worklog->user, true),
        $worklog->hours,
        $worklog->comment
    ));

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

        $worklog = UserWorkLog_new($entry['UID']);
        $worklog->hours = (($shift['end'] - $shift['start']) / 60 / 60) * $nightShiftMultiplier;
        $worklog->comment = sprintf(
            '%s (%s as %s) in %s, %s-%s',
            $shift['name'],
            $shift['title'],
            $type['name'],
            $room->name,
            Carbon::createFromTimestamp($shift['start'])->format(__('m/d/Y h:i a')),
            Carbon::createFromTimestamp($shift['end'])->format(__('m/d/Y h:i a'))
        );
        $worklog->worked_at = Carbon::createFromTimestamp($shift['start']);

        UserWorkLog_create($worklog);
    }
}

/**
 * New user work log entry
 *
 * @param int $userId
 * @return Worklog
 */
function UserWorkLog_new($userId)
{
    /** @var Carbon $buildup */
    $buildup = config('buildup_start');
    /** @var Carbon $event */
    $event = config('event_start');

    $work_date = Carbon::today();
    if (!empty($buildup) && (empty($event) || $event->lessThan(Carbon::now()))) {
        $work_date = $buildup;
    }

    $work_date
        ->setHour(0)
        ->setMinute(0)
        ->setSecond(0);

    $worklog = new Worklog();
    $worklog->user_id = $userId;
    $worklog->worked_at = $work_date;

    return $worklog;
}
