<?php

use Carbon\Carbon;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Create a new shift entry.
 */
function ShiftEntry_onCreate(ShiftEntry $shiftEntry): void
{
    $shift = $shiftEntry->shift;
    engelsystem_log(
        'User ' . User_Nick_render($shiftEntry->user, true)
        . ' signed up for shift ' . $shiftEntry->shift->title
        . ' (' . $shift->shiftType->name . ')'
        . ' at ' . $shift->location->name
        . ' from ' . $shift->start->format('Y-m-d H:i')
        . ' to ' . $shift->end->format('Y-m-d H:i')
        . ' as ' . $shiftEntry->angelType->name
    );
    mail_shift_assign($shiftEntry->user, $shift);
}

/**
 * Delete a shift entry.
 *
 * @param ShiftEntry $shiftEntry
 */
function ShiftEntry_onDelete(ShiftEntry $shiftEntry): void
{
    $signout_user = $shiftEntry->user;
    $shift = Shift($shiftEntry->shift);
    $shifttype = $shift->shiftType;
    $location = $shift->location;
    $angeltype = $shiftEntry->angelType;

    engelsystem_log(
        'Shift signout: ' . User_Nick_render($signout_user, true)
        . ' from shift ' . $shift->title
        . ' (' . $shifttype->name . ')'
        . ' at ' . $location->name
        . ' from ' . $shift->start->format('Y-m-d H:i')
        . ' to ' . $shift->end->format('Y-m-d H:i')
        . ' as ' . $angeltype->name
    );

    mail_shift_removed($signout_user, $shift);
}

/**
 * Returns next (or current) shifts of given user.
 *
 * @param User $user
 * @return ShiftEntry[]|Collection
 */
function ShiftEntries_upcoming_for_user(User $user): Collection
{
    return $user->shiftEntries()
        ->with(['shift', 'shift.shiftType'])
        ->join('shifts', 'shift_entries.shift_id', 'shifts.id')
        ->where('shifts.end', '>', Carbon::now())
        ->orderBy('shifts.end')
        ->get();
}

/**
 * Returns shifts completed by the given user.
 *
 * @param User        $user
 * @param Carbon|null $sinceTime
 * @return ShiftEntry[]|Collection
 */
function ShiftEntries_finished_by_user(User $user, ?Carbon $sinceTime = null): Collection
{
    $query = $user->shiftEntries()
        ->with(['shift', 'shift.shiftType'])
        ->join('shifts', 'shift_entries.shift_id', 'shifts.id')
        ->where('shifts.end', '<', Carbon::now())
        ->whereNull('freeloaded_by')
        ->orderByDesc('shifts.end');

    if ($sinceTime) {
        $query = $query->where('shifts.start', '>=', $sinceTime);
    }

    return $query->get();
}
