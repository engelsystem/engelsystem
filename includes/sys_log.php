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
    $logger = app('logger');

    if (isset($user)) {
        $nick = User_Nick_render($user);
    }

    $logger->info('{nick}: {message}', ['nick' => $nick, 'message' => $message]);
}
