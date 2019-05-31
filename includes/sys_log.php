<?php

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Write a log entry.
 * This should be used to log user's activity.
 *
 * @param string $message
 * @param string $level
 */
function engelsystem_log($message, $level = LogLevel::INFO)
{
    $nick = "Guest";
    /** @var LoggerInterface $logger */
    $logger = app('logger');
    $user = auth()->user();

    if ($user) {
        $nick = User_Nick_render($user, true);
    }

    $logger->log($level, '{nick}: {message}', ['nick' => $nick, 'message' => $message]);
}
