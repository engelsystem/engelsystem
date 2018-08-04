<?php

namespace Engelsystem\Exceptions;

use Engelsystem\Exceptions\Handlers\HandlerInterface;
use Engelsystem\Http\Request;
use ErrorException;
use Throwable;

class Handler
{
    /** @var string */
    protected $environment;

    /** @var HandlerInterface[] */
    protected $handler = [];

    /** @var Request */
    protected $request;

    const ENV_PRODUCTION = 'prod';
    const ENV_DEVELOPMENT = 'dev';

    /**
     * Handler constructor.
     *
     * @param string $environment prod|dev
     */
    public function __construct($environment = self::ENV_PRODUCTION)
    {
        $this->environment = $environment;
    }

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
        if (!$this->request instanceof Request) {
            $this->request = new Request();
        }

        $handler = $this->handler[$this->environment];
        $handler->report($e);
        $handler->render($this->request, $e);
        $this->terminateApplicationImmediately();
    }

    /**
     * Exit the application
     *
     * @codeCoverageIgnore
     * @param string $message
     */
    protected function terminateApplicationImmediately($message = '')
    {
        echo $message;
        die(1);
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param string $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * @param string $environment
     * @return HandlerInterface|HandlerInterface[]
     */
    public function getHandler($environment = null)
    {
        if (!is_null($environment)) {
            return $this->handler[$environment];
        }

        return $this->handler;
    }

    /**
     * @param string           $environment
     * @param HandlerInterface $handler
     */
    public function setHandler($environment, HandlerInterface $handler)
    {
        $this->handler[$environment] = $handler;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }
}
