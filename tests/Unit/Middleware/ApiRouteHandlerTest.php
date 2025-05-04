<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Exceptions\Handler;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Middleware\ApiRouteHandler;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\TestCase;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiRouteHandlerTest extends TestCase
{
    public function provideIsApi(): array
    {
        return [
            ['/foo', false],
            ['/lorem/api', false],
            ['/apiDocs', false],
            ['/api', true],
            ['/api/', true],
            ['/api/lorem', true],
            ['/api/v1/testing', true],
        ];
    }

    public function provideIsApiAccessiblePath(): array
    {
        return [
            ...$this->provideIsApi(),
            ['/metrics', true, false],
            ['/metrics/test', false, false],
            ['/health', true, false],
        ];
    }

    /**
     * @covers       \Engelsystem\Middleware\ApiRouteHandler::process
     * @covers       \Engelsystem\Middleware\ApiRouteHandler::processApi
     * @covers       \Engelsystem\Middleware\ApiRouteHandler::__construct
     * @dataProvider provideIsApi
     */
    public function testProcessIsApi(string $uri, bool $isApi): void
    {
        $request = Request::create($uri);
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        $response = new Response('response content');

        $handler->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (ServerRequestInterface $request) use ($response, $isApi) {
                $this->assertEquals($isApi, $request->getAttribute('route-api'));
                return $response;
            });

        $middleware = new ApiRouteHandler();
        $apiResponse = $middleware->process($request, $handler);

        if ($isApi) {
            $this->assertEquals('application/json', $apiResponse->getHeaderLine('content-type'));
            $this->assertEquals('*', $apiResponse->getHeaderLine('access-control-allow-origin'));
            $this->assertEquals('{"message":"response content"}', (string) $apiResponse->getBody());
            $this->assertNotEmpty($apiResponse->getHeaderLine('Etag'));
        } else {
            $this->assertEquals($response, $apiResponse);
        }
    }

    /**
     * @covers       \Engelsystem\Middleware\ApiRouteHandler::process
     * @dataProvider provideIsApiAccessiblePath
     */
    public function testProcessIsApiAccessiblePath(string $uri, bool $isApiAccessible, bool $isOnlyApi = true): void
    {
        $request = Request::create($uri);
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        $response = new Response('response content');

        $handler->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (ServerRequestInterface $request) use ($response, $isApiAccessible) {
                $this->assertEquals($isApiAccessible, $request->getAttribute('route-api-accessible'));
                return $response;
            });

        $middleware = new ApiRouteHandler();
        $apiResponse = $middleware->process($request, $handler);

        if (!$isOnlyApi) {
            $this->assertEquals($response, $apiResponse);
        }
    }

    /**
     * @covers \Engelsystem\Middleware\ApiRouteHandler::processApi
     */
    public function testProcessApiModelNotFoundException(): void
    {
        $request = Request::create('/api/test');
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);

        $handler->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (): void {
                throw new ModelNotFoundException(User::class);
            });

        $middleware = new ApiRouteHandler();
        $response = $middleware->process($request, $handler);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('{"message":"Not Found"}', (string) $response->getBody());
    }

    /**
     * @covers \Engelsystem\Middleware\ApiRouteHandler::processApi
     */
    public function testProcessApiHttpException(): void
    {
        $request = Request::create('/api/test');
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);

        $handler->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (): void {
                throw new HttpNotFound();
            });

        $middleware = new ApiRouteHandler();
        $response = $middleware->process($request, $handler);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('{"message":"Not Found"}', (string) $response->getBody());
    }

    /**
     * @covers \Engelsystem\Middleware\ApiRouteHandler::processApi
     */
    public function testProcessGenericException(): void
    {
        $e = new Exception();
        $request = Request::create('/api/test');
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        $errorHandler = $this->createMock(Handler::class);
        $this->setExpects($errorHandler, 'exceptionHandler', [$e, false], '', $this->once());
        $this->app->instance('error.handler', $errorHandler);

        $handler->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function () use ($e): void {
                throw $e;
            });

        $middleware = new ApiRouteHandler();
        $response = $middleware->process($request, $handler);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('{"message":"Internal Server Error"}', (string) $response->getBody());
    }
}
