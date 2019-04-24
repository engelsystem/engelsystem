<?php

namespace Engelsystem\Exceptions\Handlers;

use Engelsystem\Http\Request;
use Throwable;

class Legacy implements HandlerInterface
{
    /**
     * @param Request   $request
     * @param Throwable $e
     */
    public function render($request, Throwable $e)
    {
        echo 'An <del>un</del>expected error occurred, a team of untrained monkeys has been dispatched to deal with it.';
    }

    /**
     * @param Throwable $e
     */
    public function report(Throwable $e)
    {
        error_log(sprintf(
            'Exception: Code: %s, Message: %s, File: %s:%u, Trace: %s',
            $e->getCode(),
            $e->getMessage(),
            $this->stripBasePath($e->getFile()),
            $e->getLine(),
            json_encode($e->getTrace())
        ));
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
