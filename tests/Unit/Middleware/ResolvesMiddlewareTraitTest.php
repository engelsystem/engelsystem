<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Application;
use Engelsystem\Middleware\CallableHandler;
use Engelsystem\Middleware\ResolvesMiddlewareTrait;
use Engelsystem\Test\Unit\Middleware\Stub\HasStaticMethod;
use Engelsystem\Test\Unit\Middleware\Stub\ResolvesMiddlewareTraitImplementation;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use stdClass;

#[CoversMethod(ResolvesMiddlewareTrait::class, 'isMiddleware')]
#[CoversMethod(ResolvesMiddlewareTrait::class, 'resolveMiddleware')]
class ResolvesMiddlewareTraitTest extends TestCase
{
    public function testResolveMiddleware(): void
    {
        $container = $this->createStub(Application::class);
        $middlewareInterface = $this->getStubBuilder(MiddlewareInterface::class)->getStub();
        $callable = [HasStaticMethod::class, 'foo'];

        $container
            ->method('make')
            ->willReturnMap([
                ['FooBarClass', [], $middlewareInterface],
                [CallableHandler::class, ['callable' => $callable], $middlewareInterface],
                ['UnresolvableClass', [], null],
            ]);

        $middleware = new ResolvesMiddlewareTraitImplementation($container);

        $return = $middleware->callResolveMiddleware('FooBarClass');
        $this->assertEquals($middlewareInterface, $return);

        $return = $middleware->callResolveMiddleware($callable);
        $this->assertEquals($middlewareInterface, $return);

        $this->expectException(InvalidArgumentException::class);
        $middleware->callResolveMiddleware('UnresolvableClass');
    }

    public function testResolveMiddlewareNotCallable(): void
    {
        $container = $this->createStub(Application::class);

        $middleware = new ResolvesMiddlewareTraitImplementation($container);

        $this->expectException(InvalidArgumentException::class);
        $middleware->callResolveMiddleware([new stdClass(), 'test']);
    }

    public function testResolveMiddlewareNoContainer(): void
    {
        $middlewareInterface = $this->getStubBuilder(MiddlewareInterface::class)->getStub();

        $middleware = new ResolvesMiddlewareTraitImplementation();
        $return = $middleware->callResolveMiddleware($middlewareInterface);

        $this->assertEquals($middlewareInterface, $return);

        $this->expectException(InvalidArgumentException::class);
        $middleware->callResolveMiddleware('FooBarClass');
    }
}
