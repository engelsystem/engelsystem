<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Exceptions\Handlers;

use Engelsystem\Application;
use Engelsystem\Exceptions\Handlers\Whoops;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Test\Unit\TestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversMethod;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as WhoopsRunner;
use Whoops\RunInterface as WhoopsRunnerInterface;

#[CoversMethod(Whoops::class, '__construct')]
#[CoversMethod(Whoops::class, 'render')]
#[CoversMethod(Whoops::class, 'getPrettyPageHandler')]
#[CoversMethod(Whoops::class, 'getJsonResponseHandler')]
#[CoversMethod(Whoops::class, 'getData')]
class WhoopsTest extends TestCase
{
    public function testRender(): void
    {
        $app = $this->createMock(Application::class);
        $auth = $this->createMock(Authenticator::class);
        $request = $this->createMock(Request::class);
        $whoopsRunner = $this->getMockBuilder(WhoopsRunnerInterface::class)->getMock();
        $prettyPageHandler = $this->createMock(PrettyPageHandler::class);
        $jsonResponseHandler = $this->createMock(JsonResponseHandler::class);
        $exception = $this->createStub(Exception::class);

        $this->setExpects($request, 'isXmlHttpRequest', null, true);

        $this->setExpects($prettyPageHandler, 'setApplicationPaths');
        $this->setExpects($prettyPageHandler, 'addDataTable');

        $this->setExpects($jsonResponseHandler, 'setJsonApi', [true]);
        $this->setExpects($jsonResponseHandler, 'addTraceToOutput', [true]);

        $app->method('make')
            ->willReturnMap([
                [WhoopsRunner::class, $whoopsRunner],
                [PrettyPageHandler::class, $prettyPageHandler],
                [JsonResponseHandler::class, $jsonResponseHandler],
            ]);
        $app->expects($this->once())
            ->method('has')
            ->with('authenticator')
            ->willReturn(true);
        $app->expects($this->once())
            ->method('get')
            ->with('authenticator')
            ->willReturn($auth);

        $auth->expects($this->once())
            ->method('user')
            ->willReturn(null);

        $matcher = $this->exactly(2);
        $whoopsRunner
            ->expects($matcher)
            ->method('pushHandler')
            ->willReturnCallback(function (...$parameters) use (
                $matcher,
                $prettyPageHandler,
                $jsonResponseHandler
            ): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame($prettyPageHandler, $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame($jsonResponseHandler, $parameters[0]);
                }
            });
        $this->setExpects($whoopsRunner, 'writeToOutput', [false]);
        $this->setExpects($whoopsRunner, 'allowQuit', [false]);
        $this->setExpects($whoopsRunner, 'handleException', [$exception]);

        $handler = new Whoops($app);
        $handler->render($request, $exception);
    }
}
