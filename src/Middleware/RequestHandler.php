<?php

namespace Engelsystem\Middleware;

use Engelsystem\Application;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandler implements MiddlewareInterface
{
    use ResolvesMiddlewareTrait;

    /** @var Application */
    protected $container;

    /**
     * @param Application $container
     */
    public function __construct(Application $container)
    {
        $this->container = $container;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestHandler = $request->getAttribute('route-request-handler');
        $requestHandler = $this->resolveMiddleware($requestHandler);

        if ($requestHandler instanceof MiddlewareInterface) {
            return $requestHandler->process($request, $handler);
        }

        if ($requestHandler instanceof RequestHandlerInterface) {
            return $requestHandler->handle($request);
        }

        throw new InvalidArgumentException('Unable to process request handler of type ' . gettype($requestHandler));
    }
}
