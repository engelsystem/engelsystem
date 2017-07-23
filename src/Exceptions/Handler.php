<?php

namespace Engelsystem\Exceptions;

use Exception;

class Handler
{
    /** @var string */
    protected $environment;

    const ENV_PRODUCTION = 'prod';
    const ENV_DEVELOPMENT = 'dev';

    /**
     * Handler constructor.
     *
     * @param string $environment production|development
     */
    public function __construct($environment = self::ENV_PRODUCTION)
    {
        $this->environment = $environment;

        set_error_handler([$this, 'errorHandler']);
        set_exception_handler([$this, 'exceptionHandler']);
    }

    /**
     * @param int    $number
     * @param string $string
     * @param string $file
     * @param int    $line
     * @param array  $context
     */
    public function errorHandler($number, $string, $file, $line, $context)
    {
        $this->handle('error', $number, $string, $file, $line, $context);
    }

    /**
     * @param Exception $e
     */
    public function exceptionHandler(Exception $e)
    {
        $this->handle(
            'exception',
            $e->getCode(),
            get_class($e) . ': ' . $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            ['exception' => $e]
        );
    }

    /**
     * @param string $type
     * @param int    $number
     * @param string $string
     * @param string $file
     * @param int    $line
     * @param array  $context
     */
    protected function handle($type, $number, $string, $file, $line, $context = [])
    {
        error_log(sprintf('%s: Number: %s, String: %s, File: %s:%u, Context: %s',
            $type,
            $number,
            $string,
            $file,
            $line,
            json_encode($context)
        ));

        if ($this->environment == self::ENV_DEVELOPMENT) {
            echo '<pre style="background-color:#333;color:#ccc;z-index:1000;position:absolute;top:1em;padding:1em;width:97%;overflow-y:auto;">';
            echo sprintf('%s: (%s)' . PHP_EOL, ucfirst($type), $number);
            var_export([
                'string'  => $string,
                'file'    => $file . ':' . $line,
                'context' => ($this->environment == self::ENV_DEVELOPMENT ? $context : null),
            ]);
            echo '</pre>';
            die();
        }

        echo 'An <del>un</del>expected error occurred, a team of untrained monkeys has been dispatched to deal with it.';
        die();
    }

    /**
     * @param string $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }
}
