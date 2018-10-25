<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\AuthController;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGeneratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthControllerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Controllers\AuthController::__construct
     * @covers \Engelsystem\Controllers\AuthController::logout
     */
    public function testLogout()
    {
        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);
        /** @var SessionInterface|MockObject $session */
        $session = $this->getMockForAbstractClass(SessionInterface::class);
        /** @var UrlGeneratorInterface|MockObject $url */
        $url = $this->getMockForAbstractClass(UrlGeneratorInterface::class);

        $session->expects($this->once())
            ->method('invalidate');

        $response->expects($this->once())
            ->method('redirectTo')
            ->with('https://foo.bar/');

        $url->expects($this->once())
            ->method('to')
            ->with('/')
            ->willReturn('https://foo.bar/');

        $controller = new AuthController($response, $session, $url);
        $controller->logout();
    }
}
