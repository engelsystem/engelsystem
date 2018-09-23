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
        $requestHandler = $this->resolveRequestHandler($requestHandler);

        if ($requestHandler instanceof MiddlewareInterface) {
            return $requestHandler->process($request, $handler);
        }

        if ($requestHandler instanceof RequestHandlerInterface) {
            return $requestHandler->handle($request);
        }

        throw new InvalidArgumentException('Unable to process request handler of type ' . gettype($requestHandler));
    }

    /**
     * @param string|callable|MiddlewareInterface|RequestHandlerInterface $handler
     * @return MiddlewareInterface|RequestHandlerInterface
     */
    protected function resolveRequestHandler($handler)
    {
        if (is_string($handler) && strpos($handler, '@') !== false) {
            list($class, $method) = explode('@', $handler, 2);
            if (!class_exists($class) && !$this->container->has($class)) {
                $class = sprintf('Engelsystem\\Controllers\\%s', $class);
            }

            $handler = [$class, $method];
        }

        if (
            is_array($handler)
            && is_string($handler[0])
            && (
                class_exists($handler[0])
                || $this->container->has($handler[0])
            )
        ) {
            $handler[0] = $this->container->make($handler[0]);
        }

        return $this->resolveMiddleware($handler);
    }
}
