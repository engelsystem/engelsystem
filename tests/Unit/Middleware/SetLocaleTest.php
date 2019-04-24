<?php

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Helpers\Translator;
use Engelsystem\Middleware\SetLocale;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class SetLocaleTest extends TestCase
{
    /**
     * @covers \Engelsystem\Middleware\SetLocale::__construct
     * @covers \Engelsystem\Middleware\SetLocale::process
     */
    public function testRegister()
    {
        /** @var Translator|MockObject $translator */
        $translator = $this->createMock(Translator::class);
        /** @var Session|MockObject $session */
        $session = $this->createMock(Session::class);
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        /** @var ResponseInterface|MockObject $response */
        $response = $this->getMockForAbstractClass(ResponseInterface::class);

        $locale = 'te_ST.UTF8';

        $request->expects($this->exactly(3))
            ->method('getQueryParams')
            ->willReturnOnConsecutiveCalls(
                [],
                ['set-locale' => 'en_US.UTF8'],
                ['set-locale' => $locale]
            );

        $translator->expects($this->exactly(2))
            ->method('hasLocale')
            ->withConsecutive(
                ['en_US.UTF8'],
                [$locale]
            )
            ->willReturnOnConsecutiveCalls(
                false,
                true
            );
        $translator->expects($this->once())
            ->method('setLocale')
            ->with($locale);

        $session->expects($this->once())
            ->method('set')
            ->with('locale', $locale);

        $handler->expects($this->exactly(3))
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $middleware = new SetLocale($translator, $session);
        $middleware->process($request, $handler);
        $middleware->process($request, $handler);
        $middleware->process($request, $handler);
    }
}
