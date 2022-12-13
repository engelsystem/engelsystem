<?php

namespace Engelsystem\Logger;

use Engelsystem\Models\LogEntry;
use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use Stringable;
use Throwable;

class Logger extends AbstractLogger
{
    protected $allowedLevels = [
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::DEBUG,
        LogLevel::EMERGENCY,
        LogLevel::ERROR,
        LogLevel::INFO,
        LogLevel::NOTICE,
        LogLevel::WARNING,
    ];

    /** @var LogEntry */
    protected $log;

    public function __construct(LogEntry $log)
    {
        $this->log = $log;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param array             $context
     *
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

        $this->log->create(['level' => $level, 'message' => $message]);
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param array  $context
     * @return string
     */
    protected function interpolate(string $message, array $context = []): string
    {
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (is_array($val) || (is_object($val) && !method_exists($val, '__toString'))) {
                continue;
            }

            // replace the values of the message
            $message = str_replace('{' . $key . '}', (string)$val, $message);
        }

        return $message;
    }

    /**
     * @return string
     */
    protected function formatException(Throwable $e): string
    {
        return sprintf(
            implode(PHP_EOL, ['', 'Exception: %s', 'File: %s:%u', 'Code: %s', 'Trace:', '%s']),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getCode(),
            $e->getTraceAsString()
        );
    }

    /**
     * @return bool
     */
    protected function checkLevel(string $level): bool
    {
        return in_array($level, $this->allowedLevels);
    }
}
