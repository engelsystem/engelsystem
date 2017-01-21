<?php

/**
 * Write a log entry.
 * This should be used to log user's activity.
 *
 * @param string $message
 */
function engelsystem_log($message)
{
    global $user;

    $nick = "Guest";
    if (isset($user)) {
        $nick = User_Nick_render($user);
    }
    LogEntry_create($nick, $message);
}
