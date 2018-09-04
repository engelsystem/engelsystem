<?php

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Middleware\NotFoundResponse;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NotFoundResponseTest extends TestCase
{
    /**
     * @covers \Engelsystem\Middleware\NotFoundResponse::process
     */
    public function testRegister()
    {
        /** @var NotFoundResponse|MockObject $middleware */
        $middleware = $this->getMockBuilder(NotFoundResponse::class)
            ->setMethods(['renderPage'])
            ->getMock();
        /** @var ResponseInterface|MockObject $response */
        $response = $this->getMockForAbstractClass(ResponseInterface::class);
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);

        $middleware->expects($this->once())
            ->method('renderPage')
            ->willReturn($response);

        $handler->expects($this->never())
            ->method('handle');

        $middleware->process($request, $handler);
    }
}
