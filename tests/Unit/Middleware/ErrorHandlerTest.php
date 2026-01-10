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
use PHPUnit\Framework\Attributes\CoversMethod;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Twig\Loader\LoaderInterface as TwigLoader;

#[CoversMethod(ErrorHandler::class, '__construct')]
#[CoversMethod(ErrorHandler::class, 'process')]
#[CoversMethod(ErrorHandler::class, 'selectView')]
#[CoversMethod(ErrorHandler::class, 'redirectBack')]
class ErrorHandlerTest extends TestCase
{
    public function testProcess(): void
    {
        $twigLoader = $this->createMock(TwigLoader::class);
        $request = $this->createStub(ServerRequestInterface::class);
        $psrResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
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

        $matcher = $this->exactly(4);
        $twigLoader->expects($matcher)
            ->method('exists')->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('errors/418', $parameters[0]);
                    return false;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('errors/4', $parameters[0]);
                    return false;
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame('errors/400', $parameters[0]);
                    return false;
                }
                if ($matcher->numberOfInvocations() === 4) {
                    $this->assertSame('errors/505', $parameters[0]);
                    return true;
                }
            });

        $response->expects($this->exactly(2))
            ->method('getContent')
            ->willReturnOnConsecutiveCalls(
                'Teapot',
                'Internal Error!'
            );

        $matcher = $this->exactly(2);
        $response->expects($matcher)
            ->method('withView')->willReturnCallback(function (...$parameters) use ($matcher, $response) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('errors/default', $parameters[0]);
                    $this->assertSame(['status' => 418, 'content' => 'Teapot'], $parameters[1]);
                    $this->assertSame(418, $parameters[2]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('errors/505', $parameters[0]);
                    $this->assertSame(['status' => 505, 'content' => 'Internal Error!'], $parameters[1]);
                    $this->assertSame(505, $parameters[2]);
                }
                return $response;
            });

        $errorHandler->process($request, $returnResponseHandler);
        $errorHandler->process($request, $returnResponseHandler);
        $errorHandler->process($request, $returnResponseHandler);
    }

    public function testProcessHttpException(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $psrResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
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

    public function testProcessValidationException(): void
    {
        $twigLoader = $this->createStub(TwigLoader::class);
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
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

    public function testProcessModelNotFoundException(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $psrResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
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

    public function testProcessContentTypeSniffer(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $twigLoader = $this->createStub(TwigLoader::class);
        $response = new Response('<!DOCTYPE html><html lang="en"><body><h1>Hi!</h1></body></html>', 500);
        $returnResponseHandler = new ReturnResponseMiddlewareHandler($response);

        $errorHandler = new ErrorHandler($twigLoader);

        $return = $errorHandler->process($request, $returnResponseHandler);
        $this->assertEquals($response, $return);
    }
}
