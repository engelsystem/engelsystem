<?php

namespace Engelsystem\Test\Unit\Middleware\Stub;

use Engelsystem\Application;
use Engelsystem\Middleware\ResolvesMiddlewareTrait;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ResolvesMiddlewareTraitImplementation
{
    use ResolvesMiddlewareTrait;

    /** @var Application */
    protected $container;

    public function __construct(Application $container = null)
    {
        $this->container = $container;
    }

    public function callResolveMiddleware(
        string|callable|MiddlewareInterface|RequestHandlerInterface $middleware
    ): MiddlewareInterface|RequestHandlerInterface {
        return $this->resolveMiddleware($middleware);
    }
}
