<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Container\Container;
use Engelsystem\Http\Response;
use Engelsystem\Middleware\CallableHandler;
use Engelsystem\Test\Unit\Middleware\Stub\HasStaticMethod;
use Engelsystem\Test\Utils\ClosureMock;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversMethod(CallableHandler::class, '__construct')]
#[CoversMethod(CallableHandler::class, 'getCallable')]
#[CoversMethod(CallableHandler::class, 'process')]
#[CoversMethod(CallableHandler::class, 'handle')]
#[CoversMethod(CallableHandler::class, 'execute')]
#[AllowMockObjectsWithoutExpectations]
class CallableHandlerTest extends TestCase
{
    public static function provideCallable(): array
    {
        return [
            [function (): void {
            }],
            [[new class {
                public function provideCallable(): void
                {
                }
            }, 'provideCallable']],
            [[HasStaticMethod::class, 'foo']],
        ];
    }

    #[DataProvider('provideCallable')]
    public function testInit(callable $callable): void
    {
        $handler = new CallableHandler($callable);

        $this->assertEquals($callable, $handler->getCallable());
    }

    public function testProcess(): void
    {
        list($request, $response, $callable, $handler) = $this->getMocks();

        $callable->expects($this->once())
            ->method('__invoke')
            ->with($request, $handler)
            ->willReturn($response);

        $middleware = new CallableHandler($callable);
        $middleware->process($request, $handler);
    }

    public function testHandler(): void
    {
        list($request, $response, $callable) = $this->getMocks();

        $callable->expects($this->once())
            ->method('__invoke')
            ->with($request)
            ->willReturn($response);

        $middleware = new CallableHandler($callable);
        $middleware->handle($request);
    }

    public function testExecute(): void
    {
        list($request, $response, $callable) = $this->getMocks();
        $container = $this->createMock(Container::class);

        $callable->expects($this->exactly(3))
            ->method('__invoke')
            ->with($request)
            ->willReturnOnConsecutiveCalls($response, 'Lorem ipsum?', 'I\'m not an exception!');

        $container->expects($this->once())
            ->method('get')
            ->with('response')
            ->willReturn($response);

        $response->expects($this->once())
            ->method('withContent')
            ->with('Lorem ipsum?')
            ->willReturn($response);

        $middleware = new CallableHandler($callable, $container);
        $return = $middleware->handle($request);
        $this->assertInstanceOf(ResponseInterface::class, $return);
        $this->assertEquals($response, $return);

        $return = $middleware->handle($request);
        $this->assertInstanceOf(ResponseInterface::class, $return);
        $this->assertEquals($response, $return);

        $middleware = new CallableHandler($callable);
        $this->expectException(InvalidArgumentException::class);
        $middleware->handle($request);
    }

    /**
     * @return array{
     *     ServerRequestInterface&MockObject,
     *     ResponseInterface&MockObject,
     *     callable&MockObject,
     *     RequestHandlerInterface&MockObject
     * }
     */
    protected function getMocks(): array
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(Response::class);
        $callable = $this->createPartialMock(ClosureMock::class, ['__invoke']);
        return [$request, $response, $callable, $handler];
    }
}
