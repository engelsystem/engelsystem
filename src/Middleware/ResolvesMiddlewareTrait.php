<?php

namespace Engelsystem\Middleware;

use Engelsystem\Application;
use InvalidArgumentException;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

trait ResolvesMiddlewareTrait
{
    /**
     * Resolve the middleware with the container
     *
     * @param string|callable|MiddlewareInterface|RequestHandlerInterface $middleware
     * @return MiddlewareInterface|RequestHandlerInterface
     */
    protected function resolveMiddleware($middleware)
    {
        if ($this->isMiddleware($middleware)) {
            return $middleware;
        }

        if (!property_exists($this, 'container') || !$this->container instanceof Application) {
            throw new InvalidArgumentException('Unable to resolve middleware');
        }

        /** @var Application $container */
        $container = $this->container;

        if (is_string($middleware)) {
            $middleware = $container->make($middleware);
        }

        if (is_callable($middleware)) {
            $middleware = $container->make(CallableHandler::class, ['callable' => $middleware]);
        }

        if ($this->isMiddleware($middleware)) {
            return $middleware;
        }

        throw new InvalidArgumentException('Unable to resolve middleware');
    }

    /**
     * Checks if the given object is a middleware or middleware or request handler
     *
     * @param mixed $middleware
     * @return bool
     */
    protected function isMiddleware($middleware)
    {
        return ($middleware instanceof MiddlewareInterface || $middleware instanceof RequestHandlerInterface);
    }
}
