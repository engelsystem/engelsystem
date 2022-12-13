<?php

namespace Engelsystem\Exceptions;

use Engelsystem\Exceptions\Handlers\HandlerInterface;
use Engelsystem\Exceptions\Handlers\Legacy;
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

    /** @var string */
    public const ENV_PRODUCTION = 'prod';

    /** @var string */
    public const ENV_DEVELOPMENT = 'dev';

    /**
     * Handler constructor.
     *
     * @param string $environment prod|dev
     */
    public function __construct(string $environment = self::ENV_PRODUCTION)
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

    public function errorHandler(int $number, string $message, string $file, int $line)
    {
        $exception = new ErrorException($message, 0, $number, $file, $line);
        $this->exceptionHandler($exception);
    }

    /**
     * @return string
     */
    public function exceptionHandler(Throwable $e, bool $return = false)
    {
        if (!$this->request instanceof Request) {
            $this->request = new Request();
        }

        $handler = isset($this->handler[$this->environment]) ? $this->handler[$this->environment] : new Legacy();
        $handler->report($e);
        ob_start();
        $handler->render($this->request, $e);

        if ($return) {
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }

        http_response_code(500);
        ob_end_flush();

        $this->terminateApplicationImmediately();

        return '';
    }

    /**
     * Exit the application
     *
     * @codeCoverageIgnore
     */
    protected function terminateApplicationImmediately(string $message = '')
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

    public function setEnvironment(string $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return HandlerInterface|HandlerInterface[]
     */
    public function getHandler(string $environment = null)
    {
        if (!is_null($environment)) {
            return $this->handler[$environment];
        }

        return $this->handler;
    }

    public function setHandler(string $environment, HandlerInterface $handler)
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

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }
}
