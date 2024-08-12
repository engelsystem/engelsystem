<?php

declare(strict_types=1);

namespace Engelsystem\Middleware;

use Engelsystem\Application;
use InvalidArgumentException;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

trait ResolvesMiddlewareTrait
{
    /**
     * Resolve the middleware with the container
     */
    protected function resolveMiddleware(
        string|callable|array|MiddlewareInterface|RequestHandlerInterface $middleware
    ): MiddlewareInterface|RequestHandlerInterface {
        if ($this->isMiddleware($middleware)) {
            return $middleware;
        }

        if (!property_exists($this, 'container') || !$this->container instanceof Application) {
            throw new InvalidArgumentException('Unable to resolve container for middleware');
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

        throw new InvalidArgumentException('Unable to resolve middleware or callable');
    }

    /**
     * Checks if the given object is a middleware or request handler
     */
    protected function isMiddleware(mixed $middleware): bool
    {
        return ($middleware instanceof MiddlewareInterface || $middleware instanceof RequestHandlerInterface);
    }
}
