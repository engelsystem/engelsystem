<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware\Stub;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ReturnResponseMiddleware implements MiddlewareInterface
{
    public function __construct(protected ResponseInterface $response)
    {
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * Could be used to group middleware
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        return $this->response;
    }
}
