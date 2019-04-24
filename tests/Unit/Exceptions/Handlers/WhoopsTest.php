<?php

namespace Engelsystem\Test\Unit\Exceptions\handlers;

use Engelsystem\Application;
use Engelsystem\Exceptions\Handlers\Whoops;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as WhoopsRunner;
use Whoops\RunInterface as WhoopsRunnerInterface;

class WhoopsTest extends TestCase
{
    /**
     * @covers \Engelsystem\Exceptions\Handlers\Whoops
     */
    public function testRender()
    {
        /** @var Application|MockObject $app */
        $app = $this->createMock(Application::class);
        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        /** @var Request|MockObject $request */
        $request = $this->createMock(Request::class);
        /** @var WhoopsRunnerInterface|MockObject $whoopsRunner */
        $whoopsRunner = $this->getMockForAbstractClass(WhoopsRunnerInterface::class);
        /** @var PrettyPageHandler|MockObject $prettyPageHandler */
        $prettyPageHandler = $this->createMock(PrettyPageHandler::class);
        /** @var JsonResponseHandler|MockObject $jsonResponseHandler */
        $jsonResponseHandler = $this->createMock(JsonResponseHandler::class);
        /** @var Exception|MockObject $exception */
        $exception = $this->createMock(Exception::class);

        $request->expects($this->once())
            ->method('isXmlHttpRequest')
            ->willReturn(true);

        $prettyPageHandler
            ->expects($this->atLeastOnce())
            ->method('setApplicationPaths');
        $prettyPageHandler
            ->expects($this->once())
            ->method('setApplicationPaths');
        $prettyPageHandler
            ->expects($this->once())
            ->method('addDataTable');

        $jsonResponseHandler->expects($this->once())
            ->method('setJsonApi')
            ->with(true);
        $jsonResponseHandler->expects($this->once())
            ->method('addTraceToOutput')
            ->with(true);

        $app->expects($this->exactly(3))
            ->method('make')
            ->withConsecutive(
                [WhoopsRunner::class],
                [PrettyPageHandler::class],
                [JsonResponseHandler::class]
            )
            ->willReturnOnConsecutiveCalls(
                $whoopsRunner,
                $prettyPageHandler,
                $jsonResponseHandler
            );
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

        $whoopsRunner
            ->expects($this->exactly(2))
            ->method('pushHandler')
            ->withConsecutive(
                [$prettyPageHandler],
                [$jsonResponseHandler]
            );
        $whoopsRunner
            ->expects($this->once())
            ->method('writeToOutput')
            ->with(false);
        $whoopsRunner
            ->expects($this->once())
            ->method('allowQuit')
            ->with(false);
        $whoopsRunner
            ->expects($this->once())
            ->method('handleException')
            ->with($exception);

        $handler = new Whoops($app);
        $handler->render($request, $exception);
    }
}
