<?php

namespace Engelsystem\Exceptions;

use Engelsystem\Exceptions\Handlers\HandlerInterface;
use Engelsystem\Exceptions\Handlers\Legacy;
use Engelsystem\Http\Request;
use ErrorException;
use Throwable;

class Handler
{
    /** @var HandlerInterface[] */
    protected array $handler = [];

    protected ?Request $request = null;

    /** @var string */
    public const ENV_PRODUCTION = 'prod';

    /** @var string */
    public const ENV_DEVELOPMENT = 'dev';

    /**
     * Handler constructor.
     *
     * @param string $environment prod|dev
     */
    public function __construct(protected string $environment = self::ENV_PRODUCTION)
    {
    }

    /**
     * Activate the error handler
     */
    public function register(): void
    {
        set_error_handler([$this, 'errorHandler']);
        set_exception_handler([$this, 'exceptionHandler']);
    }

    public function errorHandler(int $number, string $message, string $file, int $line): void
    {
        $exception = new ErrorException($message, 0, $number, $file, $line);
        $this->exceptionHandler($exception);
    }

    public function exceptionHandler(Throwable $e, bool $return = false): string
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
    protected function terminateApplicationImmediately(string $message = ''): void
    {
        echo $message;
        die(1);
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function setEnvironment(string $environment): void
    {
        $this->environment = $environment;
    }

    /**
     * @return HandlerInterface|HandlerInterface[]
     */
    public function getHandler(string $environment = null): HandlerInterface|array
    {
        if (!is_null($environment)) {
            return $this->handler[$environment];
        }

        return $this->handler;
    }

    public function setHandler(string $environment, HandlerInterface $handler): void
    {
        $this->handler[$environment] = $handler;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }
}
