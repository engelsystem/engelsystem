<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Middleware\SendResponseHandler;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversMethod(SendResponseHandler::class, 'process')]
class SendResponseHandlerTest extends TestCase
{
    public function testRegister(): void
    {
        $middleware = $this->getMockBuilder(SendResponseHandler::class)
            ->onlyMethods(['headersSent', 'sendHeader'])
            ->getMock();
        $request = $this->getStubBuilder(ServerRequestInterface::class)->getStub();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();

        $middleware->expects($this->atLeastOnce())
            ->method('headersSent')
            ->willReturnOnConsecutiveCalls(true, false);

        $matcher = $this->exactly(4);
        $middleware->expects($matcher)
            ->method('sendHeader')->willReturnCallback(function (...$parameters) use ($matcher): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('HTTP/0.7 505 Something went wrong!', $parameters[0]);
                    $this->assertSame(true, $parameters[1]);
                    $this->assertSame(505, $parameters[2]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('Foo: bar', $parameters[0]);
                    $this->assertSame(false, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame('lorem: ipsum', $parameters[0]);
                    $this->assertSame(false, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 4) {
                    $this->assertSame('lorem: dolor', $parameters[0]);
                    $this->assertSame(false, $parameters[1]);
                }
            });

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
