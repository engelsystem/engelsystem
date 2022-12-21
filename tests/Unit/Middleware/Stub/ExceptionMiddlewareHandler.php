<?php

namespace Engelsystem\Test\Unit\Middleware\Stub;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ExceptionMiddlewareHandler implements RequestHandlerInterface
{
    /**
     * Throws an exception
     *
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new Exception('Boooom!');
    }
}
