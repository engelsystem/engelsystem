<?php

namespace Engelsystem\Exceptions\Handlers;

use Engelsystem\Http\Request;
use Psr\Log\LoggerInterface;
use Throwable;

class Legacy implements HandlerInterface
{
    /** @var LoggerInterface */
    protected $log;

    /**
     * @param Request   $request
     * @param Throwable $e
     */
    public function render($request, Throwable $e)
    {
        echo 'An <del>un</del>expected error occurred. A team of untrained monkeys has been dispatched to fix it.';
    }

    /**
     * @param Throwable $e
     */
    public function report(Throwable $e)
    {
        $previous = $e->getPrevious();
        error_log(sprintf(
            'Exception: Code: %s, Message: %s, File: %s:%u, Previous: %s, Trace: %s',
            $e->getCode(),
            $e->getMessage(),
            $this->stripBasePath($e->getFile()),
            $e->getLine(),
            $previous ? $previous->getMessage() : 'None',
            json_encode($e->getTrace(), PHP_SAPI == 'cli' ? JSON_PRETTY_PRINT : 0)
        ));

        if (is_null($this->log)) {
            return;
        }

        try {
            $this->log->critical('', ['exception' => $e]);
        } catch (Throwable $e) {
        }
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->log = $logger;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function stripBasePath($path)
    {
        $basePath = realpath(__DIR__ . '/../../..') . '/';
        return str_replace($basePath, '', $path);
    }
}
