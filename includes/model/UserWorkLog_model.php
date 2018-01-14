<?php

use Engelsystem\Database\Db;

/**
 * Load a single work log entry.
 *
 * @param int $user_worklog_id
 * @return array
 */
function UserWorkLog($user_worklog_id)
{
    return Db::selectOne("SELECT * FROM `UserWorkLog` WHERE `id`=?", [
        $user_worklog_id
    ]);
}

/**
 * Returns all work log entries for a user.
 *
 * @param array $user
 * @return array[]
 */
function UserWorkLogsForUser($user)
{
    return Db::select("SELECT * FROM `UserWorkLog` WHERE `user_id`=? ORDER BY `created_timestamp`", [
        $user['UID']
    ]);
}

/**
 * Delete a work log entry.
 *
 * @param $userWorkLog
 * @return int
 */
function UserWorkLog_delete($userWorkLog)
{
    $user_source = User($userWorkLog['user_id']);
    $result = Db::delete("DELETE FROM `UserWorkLog` WHERE `id`=?", [
        $userWorkLog['id']
    ]);

    engelsystem_log(sprintf(
        'Delete work log for %s, %s hours, %s',
        User_Nick_render($user_source),
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
    $user_source = User($userWorkLog['user_id']);

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
            User_Nick_render($user_source),
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
    global $user;

    $user_source = User($userWorkLog['user_id']);

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
        $user['UID'],
        time()
    ]);

    engelsystem_log(sprintf('Added work log entry for %s, %s hours, %s', User_Nick_render($user_source),
        $userWorkLog['work_hours'], $userWorkLog['comment']));

    return $result;
}

/**
 * New user work log entry
 *
 * @param array[] $user
 * @return array
 */
function UserWorkLog_new($user)
{
    $work_date = parse_date('Y-m-d H:i', date('Y-m-d 00:00', time()));
    $event_config = EventConfig();
    if (!empty($event_config['buildup_start_date'])) {
        $work_date = parse_date('Y-m-d H:i', date('Y-m-d 00:00', $event_config['buildup_start_date']));
    }
    return [
        'user_id'        => $user['UID'],
        'work_timestamp' => $work_date,
        'work_hours'     => 0,
        'comment'        => ''
    ];
}
