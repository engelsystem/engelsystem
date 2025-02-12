<?php

declare(strict_types=1);

namespace Engelsystem\Logger;

use Engelsystem\Models\LogEntry;
use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use Stringable;
use Throwable;

class Logger extends AbstractLogger
{
    /** @var array<string> */
    protected array $allowedLevels = [
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::DEBUG,
        LogLevel::EMERGENCY,
        LogLevel::ERROR,
        LogLevel::INFO,
        LogLevel::NOTICE,
        LogLevel::WARNING,
    ];

    public function __construct(protected LogEntry $log)
    {
    }

    /**
     * Logs with an arbitrary level.
     */
    public function log(mixed $level, string|Stringable $message, array $context = []): void
    {
        if (!$this->checkLevel($level)) {
            throw new InvalidArgumentException('Unknown log level: ' . $level);
        }

        $message = $this->interpolate($message, $context);

        if (isset($context['exception']) && $context['exception'] instanceof Throwable) {
            $message .= $this->formatException($context['exception']);
        }

        $this->createEntry(['level' => $level, 'message' => $message]);
    }

    /**
     * Interpolates context values into the message placeholders.
     */
    protected function interpolate(string $message, array $context = []): string
    {
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (is_array($val) || (is_object($val) && !method_exists($val, '__toString'))) {
                continue;
            }

            // replace the values of the message
            $message = str_replace('{' . $key . '}', (string) $val, $message);
        }

        return $message;
    }

    protected function formatException(Throwable $e): string
    {
        return sprintf(
            implode(PHP_EOL, ['', '%s: %s', 'File: %s:%u', 'Code: %s', 'Trace:', '%s']),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getCode(),
            $e->getTraceAsString()
        );
    }

    protected function checkLevel(string $level): bool
    {
        return in_array($level, $this->allowedLevels);
    }

    protected function createEntry(array $data): void
    {
        $this->log->create($data);
    }
}
