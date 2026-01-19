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
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversMethod(ApiRouteHandler::class, 'process')]
#[CoversMethod(ApiRouteHandler::class, 'processApi')]
#[CoversMethod(ApiRouteHandler::class, '__construct')]
class ApiRouteHandlerTest extends TestCase
{
    public static function provideIsApi(): array
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

    public static function provideIsApiAccessiblePath(): array
    {
        return [
            ...static::provideIsApi(),
            ['/metrics', true, false],
            ['/metrics/test', false, false],
            ['/health', true, false],
        ];
    }

    #[DataProvider('provideIsApi')]
    public function testProcessIsApi(string $uri, bool $isApi): void
    {
        $request = Request::create($uri);
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
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

    #[DataProvider('provideIsApiAccessiblePath')]
    public function testProcessIsApiAccessiblePath(string $uri, bool $isApiAccessible, bool $isOnlyApi = true): void
    {
        $request = Request::create($uri);
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
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

    public function testProcessApiModelNotFoundException(): void
    {
        $request = Request::create('/api/test');
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();

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

    public function testProcessApiHttpException(): void
    {
        $request = Request::create('/api/test');
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();

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

    public function testProcessGenericException(): void
    {
        $e = new Exception();
        $request = Request::create('/api/test');
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
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
