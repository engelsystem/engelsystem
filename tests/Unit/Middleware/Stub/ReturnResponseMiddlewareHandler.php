<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware\Stub;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ReturnResponseMiddlewareHandler implements RequestHandlerInterface
{
    public function __construct(protected ResponseInterface $response)
    {
    }

    /**
     * Returns a given response
     *
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Set the response
     */
    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }
}
