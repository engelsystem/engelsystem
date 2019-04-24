<?php

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Middleware\SendResponseHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SendResponseHandlerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Middleware\SendResponseHandler::process
     */
    public function testRegister()
    {
        /** @var SendResponseHandler|MockObject $middleware */
        $middleware = $this->getMockBuilder(SendResponseHandler::class)
            ->setMethods(['headersSent', 'sendHeader'])
            ->getMock();
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var ResponseInterface|MockObject $response */
        $response = $this->getMockForAbstractClass(ResponseInterface::class);
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);

        $middleware->expects($this->atLeastOnce())
            ->method('headersSent')
            ->willReturnOnConsecutiveCalls(true, false);

        $middleware->expects($this->exactly(4))
            ->method('sendHeader')
            ->withConsecutive(
                ['HTTP/0.7 505 Something went wrong!', true, 505],
                ['Foo: bar', false],
                ['lorem: ipsum', false],
                ['lorem: dolor', false]
            );

        $handler->expects($this->exactly(2))
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $response->expects($this->exactly(2))
            ->method('getBody')
            ->willReturn('Lorem Ipsum!');

        $response->expects($this->atLeastOnce())
            ->method('getProtocolVersion')
            ->willReturn('0.7');

        $response->expects($this->atLeastOnce())
            ->method('getStatusCode')
            ->willReturn(505);

        $response->expects($this->once())
            ->method('getReasonPhrase')
            ->willReturn('Something went wrong!');
        $response->expects($this->once())
            ->method('getHeaders')
            ->willReturn(['Foo' => ['bar'], 'lorem' => ['ipsum', 'dolor']]);

        $this->expectOutputString('Lorem Ipsum!Lorem Ipsum!');
        $middleware->process($request, $handler);
        $middleware->process($request, $handler);
    }
}
