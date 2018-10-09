<?php

/**
 * Write a log entry.
 * This should be used to log user's activity.
 *
 * @param string $message
 */
function engelsystem_log($message)
{
    $nick = "Guest";
    $logger = app('logger');
    $user = auth()->user();

    if ($user) {
        $nick = User_Nick_render($user);
    }

    $logger->info('{nick}: {message}', ['nick' => $nick, 'message' => $message]);
}
