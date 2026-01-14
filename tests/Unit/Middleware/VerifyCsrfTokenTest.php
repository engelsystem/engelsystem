<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Http\Exceptions\HttpAuthExpired;
use Engelsystem\Middleware\VerifyCsrfToken;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[CoversMethod(VerifyCsrfToken::class, 'isReading')]
#[CoversMethod(VerifyCsrfToken::class, 'process')]
#[CoversMethod(VerifyCsrfToken::class, '__construct')]
#[CoversMethod(VerifyCsrfToken::class, 'tokensMatch')]
class VerifyCsrfTokenTest extends TestCase
{
    public function testProcess(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $response = $this->getStubBuilder(ResponseInterface::class)->getStub();

        $handler->expects($this->exactly(2))
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $middleware = $this->getMockBuilder(VerifyCsrfToken::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['tokensMatch'])
            ->getMock();

        $middleware->expects($this->exactly(2))
            ->method('tokensMatch')
            ->willReturnOnConsecutiveCalls(true, false);

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

    public function testTokensMatch(): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $response = $this->getStubBuilder(ResponseInterface::class)->getStub();
        $session = $this->getMockBuilder(SessionInterface::class)->getMock();

        $middleware = $this->getMockBuilder(VerifyCsrfToken::class)
            ->setConstructorArgs([$session])
            ->onlyMethods(['isReading'])
            ->getMock();

        $middleware->expects($this->atLeastOnce())
            ->method('isReading')
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
}
