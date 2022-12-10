<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Write a log entry.
 * This should be used to log user's activity.
 *
 * @param string $message
 * @param string $level
 */
function engelsystem_log(string $message, string $level = LogLevel::INFO): void
{
    /** @var LoggerInterface $logger */
    $logger = app('logger');
    $logger->log($level, $message);
}
