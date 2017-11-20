<?php

namespace Engelsystem\Exceptions;

use ErrorException;
use Throwable;

class BasicHandler extends Handler
{
    /**
     * Activate the error handler
     */
    public function register()
    {
        set_error_handler([$this, 'errorHandler']);
        set_exception_handler([$this, 'exceptionHandler']);
    }

    /**
     * @param int    $number
     * @param string $message
     * @param string $file
     * @param int    $line
     */
    public function errorHandler($number, $message, $file, $line)
    {
        $exception = new ErrorException($message, 0, $number, $file, $line);
        $this->exceptionHandler($exception);
    }

    /**
     * @param Throwable $e
     */
    public function exceptionHandler($e)
    {
        $this->handle(
            $e->getCode(),
            get_class($e) . ': ' . $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            ['exception' => $e]
        );
    }

    /**
     * @param int    $number
     * @param string $string
     * @param string $file
     * @param int    $line
     * @param array  $context
     * @param array  $trace
     */
    protected function handle($number, $string, $file, $line, $context = [], $trace = [])
    {
        error_log(sprintf('Exception: Number: %s, String: %s, File: %s:%u, Context: %s',
            $number,
            $string,
            $file,
            $line,
            json_encode($context)
        ));

        $file = $this->stripBasePath($file);

        if ($this->environment == self::ENV_DEVELOPMENT) {
            echo '<pre style="background-color:#333;color:#ccc;z-index:1000;position:fixed;bottom:1em;padding:1em;width:97%;max-height: 90%;overflow-y:auto;">';
            echo sprintf('%s: (%s)' . PHP_EOL, ucfirst($type), $number);
            var_export([
                'string'     => $string,
                'file'       => $file . ':' . $line,
                'context'    => $context,
                'stacktrace' => $this->formatStackTrace($trace),
            ]);
            echo '</pre>';
            die();
        }

        echo 'An <del>un</del>expected error occurred, a team of untrained monkeys has been dispatched to deal with it.';
        die();
    }

    /**
     * @param array $stackTrace
     * @return array
     */
    protected function formatStackTrace($stackTrace)
    {
        $return = [];

        foreach ($stackTrace as $trace) {
            $path = '';
            $line = '';

            if (isset($trace['file']) && isset($trace['line'])) {
                $path = $this->stripBasePath($trace['file']);
                $line = $trace['line'];
            }

            $functionName = $trace['function'];

            $return[] = [
                'file'        => $path . ':' . $line,
                $functionName => $trace['args'],
            ];
        }

        return $return;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function stripBasePath($path)
    {
        $basePath = realpath(__DIR__ . '/../..') . '/';
        return str_replace($basePath, '', $path);
    }
}
