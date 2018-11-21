<?php

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Translator;
use Engelsystem\Http\Request;
use Engelsystem\Middleware\LegacyMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class LegacyMiddlewareTest extends TestCase
{
    /**
     * @covers \Engelsystem\Middleware\LegacyMiddleware::__construct
     * @covers \Engelsystem\Middleware\LegacyMiddleware::process
     */
    public function testRegister()
    {
        /** @var ContainerInterface|MockObject $container */
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        /** @var LegacyMiddleware|MockObject $middleware */
        $middleware = $this->getMockBuilder(LegacyMiddleware::class)
            ->setConstructorArgs([$container, $auth])
            ->setMethods(['loadPage', 'renderPage'])
            ->getMock();
        /** @var Request|MockObject $defaultRequest */
        $defaultRequest = $this->createMock(Request::class);
        /** @var ParameterBag|MockObject $parameters */
        $parameters = $this->createMock(ParameterBag::class);
        /** @var ResponseInterface|MockObject $response */
        $response = $this->getMockForAbstractClass(ResponseInterface::class);
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var Translator|MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $middleware->expects($this->once())
            ->method('loadPage')
            ->with('user_worklog')
            ->willReturn(['title', 'content']);

        $middleware->expects($this->exactly(2))
            ->method('renderPage')
            ->withConsecutive(
                ['user_worklog', 'title', 'content'],
                ['404', 'Page not found', 'It\'s not available!']
            )
            ->willReturn($response);

        $container->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(['request'], ['request'], ['translator'])
            ->willReturnOnConsecutiveCalls(
                $defaultRequest,
                $defaultRequest,
                $translator
            );

        $auth->expects($this->atLeastOnce())
            ->method('can')
            ->willReturn(false);

        $translator->expects($this->exactly(2))
            ->method('translate')
            ->willReturnOnConsecutiveCalls(
                'Page not found',
                'It\'s not available!'
            );

        $defaultRequest->query = $parameters;
        $defaultRequest->expects($this->once())
            ->method('path')
            ->willReturn('user-worklog');

        $parameters->expects($this->exactly(2))
            ->method('get')
            ->with('p')
            ->willReturnOnConsecutiveCalls(
                null,
                'foo'
            );

        $middleware->process($request, $handler);
        $middleware->process($request, $handler);
    }
}
