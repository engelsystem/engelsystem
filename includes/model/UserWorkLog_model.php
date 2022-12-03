<?php

use Carbon\Carbon;
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
