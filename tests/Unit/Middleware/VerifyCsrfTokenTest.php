<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Http\Exceptions\HttpAuthExpired;
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
     * @covers \Engelsystem\Middleware\VerifyCsrfToken::isReading
     * @covers \Engelsystem\Middleware\VerifyCsrfToken::process
     */
    public function testProcess(): void
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
            ->onlyMethods(['tokensMatch', 'isExempt'])
            ->getMock();

        $middleware->expects($this->exactly(2))
            ->method('tokensMatch')
            ->willReturnOnConsecutiveCalls(true, false);

        $middleware->expects($this->exactly(2))
            ->method('isExempt')
            ->willReturn(false);

        // Results in true, false, false
        $request->expects($this->exactly(3))
            ->method('getMethod')
            ->willReturnOnConsecutiveCalls('GET', 'DELETE', 'POST');

        // Is reading
        $middleware->process($request, $handler);
        // Tokens match
        $middleware->process($request, $handler);

        // No match
        $this->expectException(HttpAuthExpired::class);
        $middleware->process($request, $handler);
    }

    /**
     * @covers \Engelsystem\Middleware\VerifyCsrfToken::__construct
     * @covers \Engelsystem\Middleware\VerifyCsrfToken::tokensMatch
     */
    public function testTokensMatch(): void
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        /** @var ResponseInterface|MockObject $response */
        $response = $this->getMockForAbstractClass(ResponseInterface::class);
        /** @var SessionInterface|MockObject $session */
        $session = $this->getMockForAbstractClass(SessionInterface::class);

        /** @var VerifyCsrfToken|MockObject $middleware */
        $middleware = $this->getMockBuilder(VerifyCsrfToken::class)
            ->setConstructorArgs([$session])
            ->onlyMethods(['isReading', 'isExempt'])
            ->getMock();

        $middleware->expects($this->atLeastOnce())
            ->method('isReading')
            ->willReturn(false);

        $middleware->expects($this->atLeastOnce())
            ->method('isExempt')
            ->willReturn(false);

        $handler->expects($this->exactly(3))
            ->method('handle')
            ->willReturn($response);

        $request->expects($this->exactly(4))
            ->method('getParsedBody')
            ->willReturnOnConsecutiveCalls(
                null,
                ['_token' => 'PostFooToken'],
                ['_token' => 'PostBarToken'],
                null
            );
        $request->expects($this->exactly(4))
            ->method('getHeader')
            ->with('X-CSRF-TOKEN')
            ->willReturnOnConsecutiveCalls(
                ['HeaderFooToken'],
                [],
                ['HeaderBarToken'],
                []
            );

        $session->expects($this->exactly(4))
            ->method('get')
            ->with('_token')
            ->willReturnOnConsecutiveCalls(
                'HeaderFooToken',
                'PostFooToken',
                'PostBarToken',
                'NotAvailableToken'
            );

        // Header token
        $middleware->process($request, $handler);
        // POST token
        $middleware->process($request, $handler);
        // Header and POST tokens
        $middleware->process($request, $handler);
        // No tokens
        $this->expectException(HttpAuthExpired::class);
        $middleware->process($request, $handler);
    }

    /**
     * @covers \Engelsystem\Middleware\VerifyCsrfToken::isExempt
     */
    public function testIsExempt(): void
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        /** @var ResponseInterface|MockObject $response */
        $response = $this->getMockForAbstractClass(ResponseInterface::class);
        /** @var SessionInterface|MockObject $session */
        $session = $this->getMockForAbstractClass(SessionInterface::class);

        $middleware = new VerifyCsrfToken($session);

        $handler->expects($this->exactly(2))
            ->method('handle')
            ->willReturn($response);

        $request->expects($this->exactly(3))
            ->method('getMethod')
            ->willReturn('POST');

        $request->expects($this->exactly(3))
            ->method('getRequestTarget')
            ->willReturnOnConsecutiveCalls(
                '/oauth2/token',
                '/oauth2/token/',
                '/other/path'
            );

        // Exempt path - should pass without token check
        $middleware->process($request, $handler);
        // Exempt path with trailing slash - should pass
        $middleware->process($request, $handler);
        // Non-exempt path - should throw exception (no token)
        $this->expectException(HttpAuthExpired::class);
        $middleware->process($request, $handler);
    }
}
