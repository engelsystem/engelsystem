<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\NotificationType;
use Engelsystem\Http\Exceptions\HttpException;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Psr7ServiceProvider;
use Engelsystem\Http\RedirectServiceProvider;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\ResponseServiceProvider;
use Engelsystem\Http\UrlGeneratorServiceProvider;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Middleware\ErrorHandler;
use Engelsystem\Test\Unit\Middleware\Stub\ReturnResponseMiddlewareHandler;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Twig\Loader\LoaderInterface as TwigLoader;

class ErrorHandlerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Middleware\ErrorHandler::__construct
     * @covers \Engelsystem\Middleware\ErrorHandler::process
     * @covers \Engelsystem\Middleware\ErrorHandler::selectView
     */
    public function testProcess(): void
    {
        /** @var TwigLoader|MockObject $twigLoader */
        $twigLoader = $this->createMock(TwigLoader::class);
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface|MockObject $psrResponse */
        $psrResponse = $this->getMockForAbstractClass(ResponseInterface::class);
        $returnResponseHandler = new ReturnResponseMiddlewareHandler($psrResponse);

        $psrResponse->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(505);
        $psrResponse->expects($this->once())
            ->method('getHeader')
            ->with('content-type')
            ->willReturn([]);

        $errorHandler = new ErrorHandler($twigLoader);

        $return = $errorHandler->process($request, $returnResponseHandler);
        $this->assertEquals($psrResponse, $return, 'Plain PSR-7 Response should be passed directly');

        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);

        $response->expects($this->exactly(4))
            ->method('getStatusCode')
            ->willReturnOnConsecutiveCalls(
                200,
                418,
                505,
                505
            );
        $response->expects($this->exactly(4))
            ->method('getHeader')
            ->willReturnOnConsecutiveCalls(
                [],
                [],
                [],
                ['application/json']
            );

        $response->expects(self::any())
            ->method('getHeaders')
            ->willReturn([]);

        $returnResponseHandler->setResponse($response);
        $return = $errorHandler->process($request, $returnResponseHandler);
        $this->assertEquals($response, $return, 'Only Responses >= 400 should be processed');

        $twigLoader->expects($this->exactly(4))
            ->method('exists')
            ->withConsecutive(
                ['errors/418'],
                ['errors/4'],
                ['errors/400'],
                ['errors/505']
            )
            ->willReturnOnConsecutiveCalls(
                false,
                false,
                false,
                true
            );

        $response->expects($this->exactly(2))
            ->method('getContent')
            ->willReturnOnConsecutiveCalls(
                'Teapot',
                'Internal Error!'
            );

        $response->expects($this->exactly(2))
            ->method('withView')
            ->withConsecutive(
                ['errors/default', ['status' => 418, 'content' => 'Teapot'], 418],
                ['errors/505', ['status' => 505, 'content' => 'Internal Error!'], 505]
            )
            ->willReturn($response);

        $errorHandler->process($request, $returnResponseHandler);
        $errorHandler->process($request, $returnResponseHandler);
        $errorHandler->process($request, $returnResponseHandler);
    }

    /**
     * @covers \Engelsystem\Middleware\ErrorHandler::process
     */
    public function testProcessHttpException(): void
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface|MockObject $psrResponse */
        $psrResponse = $this->getMockForAbstractClass(ResponseInterface::class);
        /** @var ReturnResponseMiddlewareHandler|MockObject $returnResponseHandler */
        $returnResponseHandler = $this->getMockBuilder(ReturnResponseMiddlewareHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $psrResponse->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(300);
        $psrResponse->expects($this->once())
            ->method('getHeader')
            ->with('content-type')
            ->willReturn([]);

        $returnResponseHandler->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (): void {
                throw new HttpException(300, 'Some response', ['lor' => 'em']);
            });

        /** @var ErrorHandler|MockObject $errorHandler */
        $errorHandler = $this->getMockBuilder(ErrorHandler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createResponse'])
            ->getMock();

        $errorHandler->expects($this->once())
            ->method('createResponse')
            ->with('Some response', 300, ['lor' => 'em'])
            ->willReturn($psrResponse);

        $return = $errorHandler->process($request, $returnResponseHandler);
        $this->assertEquals($psrResponse, $return);
    }

    /**
     * @covers \Engelsystem\Middleware\ErrorHandler::process
     * @covers \Engelsystem\Middleware\ErrorHandler::redirectBack
     */
    public function testProcessValidationException(): void
    {
        /** @var TwigLoader|MockObject $twigLoader */
        $twigLoader = $this->createMock(TwigLoader::class);
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        $validator = $this->createMock(Validator::class);

        $handler->expects($this->exactly(2))
            ->method('handle')
            ->willReturnCallback(function () use ($validator): void {
                throw new ValidationException($validator);
            });

        $validator->expects($this->exactly(2))
            ->method('getErrors')
            ->willReturn(['foo' => ['validation.foo.numeric']]);

        $session = new Session(new MockArraySessionStorage());
        $session->set(
            'messages.' . NotificationType::ERROR->value,
            ['validation' => ['foo' => ['validation.foo.required']]]
        );
        $request = Request::create(
            '/foo/bar',
            'POST',
            ['foo' => 'bar', 'password' => 'Test123', 'password_confirmation' => 'Test1234']
        );
        $request->setSession($session);

        $this->app->instance(Session::class, $session);
        $this->app->bind(SessionInterface::class, Session::class);
        $this->app->instance('request', $request);
        $this->app->instance('config', new Config());
        (new ResponseServiceProvider($this->app))->register();
        (new Psr7ServiceProvider($this->app))->register();
        (new RedirectServiceProvider($this->app))->register();
        (new UrlGeneratorServiceProvider($this->app))->register();

        $errorHandler = new ErrorHandler($twigLoader);
        $return = $errorHandler->process($request, $handler);

        $this->assertEquals(302, $return->getStatusCode());
        $this->assertEquals('http://localhost/', $return->getHeaderLine('location'));
        $this->assertEquals([
            'messages.' . NotificationType::ERROR->value => [
                'validation' => [
                    'foo' => [
                        'validation.foo.required',
                        'validation.foo.numeric',
                    ],
                ],
            ],
            'form-data-foo' => 'bar',
        ], $session->all());

        $request = $request->withAddedHeader('referer', '/foo/batz');
        $this->app->instance(Request::class, $request);
        $return = $errorHandler->process($request, $handler);

        $this->assertEquals('http://localhost/foo/batz', $return->getHeaderLine('location'));
    }

    /**
     * @covers \Engelsystem\Middleware\ErrorHandler::process
     */
    public function testProcessModelNotFoundException(): void
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface|MockObject $psrResponse */
        $psrResponse = $this->getMockForAbstractClass(ResponseInterface::class);
        /** @var ReturnResponseMiddlewareHandler|MockObject $returnResponseHandler */
        $returnResponseHandler = $this->getMockBuilder(ReturnResponseMiddlewareHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $psrResponse->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(404);

        $psrResponse->expects($this->once())
            ->method('getHeader')
            ->with('content-type')
            ->willReturn([]);

        $returnResponseHandler->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (): void {
                throw new ModelNotFoundException('Some model could not be found');
            });

        /** @var ErrorHandler|MockObject $errorHandler */
        $errorHandler = $this->getMockBuilder(ErrorHandler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createResponse'])
            ->getMock();

        $errorHandler->expects($this->once())
            ->method('createResponse')
            ->with('', 404)
            ->willReturn($psrResponse);

        $return = $errorHandler->process($request, $returnResponseHandler);
        $this->assertEquals($psrResponse, $return);
    }

    /**
     * @covers \Engelsystem\Middleware\ErrorHandler::process
     */
    public function testProcessContentTypeSniffer(): void
    {
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var TwigLoader|MockObject $twigLoader */
        $twigLoader = $this->createMock(TwigLoader::class);
        $response = new Response('<!DOCTYPE html><html lang="en"><body><h1>Hi!</h1></body></html>', 500);
        $returnResponseHandler = new ReturnResponseMiddlewareHandler($response);

        /** @var ErrorHandler|MockObject $errorHandler */
        $errorHandler = new ErrorHandler($twigLoader);

        $return = $errorHandler->process($request, $returnResponseHandler);
        $this->assertEquals($response, $return);
    }
}
