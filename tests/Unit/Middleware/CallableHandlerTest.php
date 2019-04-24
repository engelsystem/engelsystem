<?php

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Container\Container;
use Engelsystem\Http\Response;
use Engelsystem\Middleware\CallableHandler;
use Engelsystem\Test\Unit\Middleware\Stub\HasStaticMethod;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;

class CallableHandlerTest extends TestCase
{
    public function provideCallable()
    {
        return [
            [function () {
            }],
            [[$this, 'provideCallable']],
            [[HasStaticMethod::class, 'foo']],
        ];
    }

    /**
     * @dataProvider provideCallable
     * @covers       \Engelsystem\Middleware\CallableHandler::__construct
     * @covers       \Engelsystem\Middleware\CallableHandler::getCallable
     * @param callable $callable
     */
    public function testInit($callable)
    {
        $handler = new CallableHandler($callable);

        $this->assertEquals($callable, $handler->getCallable());
    }

    /**
     * @covers \Engelsystem\Middleware\CallableHandler::process
     */
    public function testProcess()
    {
        /** @var ServerRequestInterface|MockObject $request */
        /** @var ResponseInterface|MockObject $response */
        /** @var callable|MockObject $callable */
        /** @var RequestHandlerInterface|MockObject $handler */
        list($request, $response, $callable, $handler) = $this->getMocks();

        $callable->expects($this->once())
            ->method('__invoke')
            ->with($request, $handler)
            ->willReturn($response);

        $middleware = new CallableHandler($callable);
        $middleware->process($request, $handler);
    }

    /**
     * @covers \Engelsystem\Middleware\CallableHandler::handle
     */
    public function testHandler()
    {
        /** @var ServerRequestInterface|MockObject $request */
        /** @var ResponseInterface|MockObject $response */
        /** @var callable|MockObject $callable */
        list($request, $response, $callable) = $this->getMocks();

        $callable->expects($this->once())
            ->method('__invoke')
            ->with($request)
            ->willReturn($response);

        $middleware = new CallableHandler($callable);
        $middleware->handle($request);
    }

    /**
     * @covers \Engelsystem\Middleware\CallableHandler::execute
     */
    public function testExecute()
    {
        /** @var ServerRequestInterface|MockObject $request */
        /** @var Response|MockObject $response */
        /** @var callable|MockObject $callable */
        list($request, $response, $callable) = $this->getMocks();
        /** @var Container|MockObject $container */
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
     * @return array
     */
    protected function getMocks(): array
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);
        /** @var callable|MockObject $callable */
        $callable = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
        return [$request, $response, $callable, $handler];
    }
}
