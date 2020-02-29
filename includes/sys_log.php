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
    /** @var LoggerInterface $logger */
    $logger = app('logger');
    $logger->log($level, $message);
}
