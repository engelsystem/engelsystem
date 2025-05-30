<?php

declare(strict_types=1);

namespace Engelsystem\Exceptions;

use Engelsystem\Environment;
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

    /**
     * Handler constructor.
     *
     * @param Environment $environment prod|dev
     */
    public function __construct(protected Environment $environment = Environment::PRODUCTION)
    {
    }

    /**
     * Activate the error handler
     * @codeCoverageIgnore
     */
    public function register(): void
    {
        if (defined('PHPUNIT_COMPOSER_INSTALL')) {
            return;
        }

        set_error_handler([$this, 'errorHandler']);
        set_exception_handler([$this, 'exceptionHandler']);
    }

    public function errorHandler(int $errorNumber, string $message, string $file, int $line): bool
    {
        $handleLevel = $this->environment == Environment::DEVELOPMENT
            ? E_ALL
            : E_RECOVERABLE_ERROR | E_COMPILE_ERROR;
        // If error level should be intercepted or ignored (like warnings in prod)
        $shouldHandle = (bool) ($errorNumber & $handleLevel);

        $exception = new ErrorException($message, 0, $errorNumber, $file, $line);
        $this->exceptionHandler($exception, $shouldHandle);

        return $shouldHandle;
    }

    public function exceptionHandler(Throwable $e, bool $showError = true): string
    {
        if (!$this->request instanceof Request) {
            $this->request = new Request();
        }

        $handler = $this->handler[$this->environment->value] ?? new Legacy();
        $handler->report($e);
        ob_start();
        $handler->render($this->request, $e);

        if (!$showError) {
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }

        if (!headers_sent()) {
            // @codeCoverageIgnoreStart
            http_response_code(500);
            // @codeCoverageIgnoreEnd
        }

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

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    public function setEnvironment(Environment $environment): void
    {
        $this->environment = $environment;
    }

    /**
     * @return HandlerInterface|HandlerInterface[]
     */
    public function getHandler(?Environment $environment = null): HandlerInterface|array
    {
        if (!is_null($environment)) {
            return $this->handler[$environment->value];
        }

        return $this->handler;
    }

    public function setHandler(Environment $environment, HandlerInterface $handler): void
    {
        $this->handler[$environment->value] = $handler;
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
