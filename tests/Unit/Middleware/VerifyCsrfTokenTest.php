<?php

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Middleware\VerifyCsrfToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class VerifyCsrfTokenTest extends TestCase
{
    /**
     * @covers \Engelsystem\Middleware\VerifyCsrfToken::process
     * @covers \Engelsystem\Middleware\VerifyCsrfToken::isReading
     */
    public function testProcess()
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        /** @var ResponseInterface|MockObject $response */
        $response = $this->getMockForAbstractClass(ResponseInterface::class);

        $handler->expects($this->exactly(2))
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        /** @var VerifyCsrfToken|MockObject $middleware */
        $middleware = $this->getMockBuilder(VerifyCsrfToken::class)
            ->disableOriginalConstructor()
            ->setMethods(['notAuthorizedResponse', 'tokensMatch'])
            ->getMock();

        $middleware->expects($this->exactly(1))
            ->method('notAuthorizedResponse')
            ->willReturn($response);

        $middleware->expects($this->exactly(2))
            ->method('tokensMatch')
            ->willReturnOnConsecutiveCalls(true, false);

        // Results in true, false, false
        $request->expects($this->exactly(3))
            ->method('getMethod')
            ->willReturnOnConsecutiveCalls('GET', 'POST', 'DELETE');

        $middleware->process($request, $handler);
        $middleware->process($request, $handler);
        $middleware->process($request, $handler);
    }

    /**
     * @covers \Engelsystem\Middleware\VerifyCsrfToken::__construct
     * @covers \Engelsystem\Middleware\VerifyCsrfToken::tokensMatch
     */
    public function testTokensMatch()
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        /** @var ResponseInterface|MockObject $response */
        $response = $this->getMockForAbstractClass(ResponseInterface::class);
        /** @var ResponseInterface|MockObject $noAuthResponse */
        $noAuthResponse = $this->getMockForAbstractClass(ResponseInterface::class);
        /** @var SessionInterface|MockObject $session */
        $session = $this->getMockForAbstractClass(SessionInterface::class);

        /** @var VerifyCsrfToken|MockObject $middleware */
        $middleware = $this->getMockBuilder(VerifyCsrfToken::class)
            ->setConstructorArgs([$session])
            ->setMethods(['isReading', 'notAuthorizedResponse'])
            ->getMock();

        $middleware->expects($this->atLeastOnce())
            ->method('isReading')
            ->willReturn(false);
        $middleware->expects($this->exactly(1))
            ->method('notAuthorizedResponse')
            ->willReturn($noAuthResponse);

        $handler->expects($this->exactly(3))
            ->method('handle')
            ->willReturn($response);

        $request->expects($this->exactly(4))
            ->method('getParsedBody')
            ->willReturnOnConsecutiveCalls(
                null,
                null,
                ['_token' => 'PostFooToken'],
                ['_token' => 'PostBarToken']
            );
        $request->expects($this->exactly(4))
            ->method('getHeader')
            ->with('X-CSRF-TOKEN')
            ->willReturnOnConsecutiveCalls(
                [],
                ['HeaderFooToken'],
                [],
                ['HeaderBarToken']
            );

        $session->expects($this->exactly(4))
            ->method('get')
            ->with('_token')
            ->willReturnOnConsecutiveCalls(
                'NotAvailableToken',
                'HeaderFooToken',
                'PostFooToken',
                'PostBarToken'
            );

        // Not tokens
        $this->assertEquals($noAuthResponse, $middleware->process($request, $handler));
        // Header token
        $this->assertEquals($response, $middleware->process($request, $handler));
        // POST token
        $this->assertEquals($response, $middleware->process($request, $handler));
        // Header and POST tokens
        $this->assertEquals($response, $middleware->process($request, $handler));
    }
}
