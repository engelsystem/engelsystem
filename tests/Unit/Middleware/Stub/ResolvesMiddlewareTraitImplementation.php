<?php

namespace Engelsystem\Test\Unit\Middleware\Stub;

use Engelsystem\Application;
use Engelsystem\Middleware\ResolvesMiddlewareTrait;
use InvalidArgumentException;
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

    /**
     * @return MiddlewareInterface|RequestHandlerInterface
     * @throws InvalidArgumentException
     */
    public function callResolveMiddleware(string|callable|MiddlewareInterface|RequestHandlerInterface $middleware)
    {
        return $this->resolveMiddleware($middleware);
    }
}
