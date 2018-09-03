<?php

namespace Engelsystem\Test\Unit\Exceptions\handlers;


use Engelsystem\Application;
use Engelsystem\Exceptions\Handlers\Whoops;
use Engelsystem\Http\Request;
use Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as Mock;
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
        /** @var Application|Mock $app */
        $app = $this->createMock(Application::class);
        /** @var Request|Mock $request */
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method('isXmlHttpRequest')
            ->willReturn(true);
        /** @var WhoopsRunnerInterface|Mock $whoopsRunner */
        $whoopsRunner = $this->getMockForAbstractClass(WhoopsRunnerInterface::class);
        /** @var PrettyPageHandler|Mock $prettyPageHandler */
        $prettyPageHandler = $this->createMock(PrettyPageHandler::class);
        $prettyPageHandler
            ->expects($this->atLeastOnce())
            ->method('setApplicationPaths');
        $prettyPageHandler
            ->expects($this->once())
            ->method('setApplicationPaths');
        $prettyPageHandler
            ->expects($this->once())
            ->method('addDataTable');
        /** @var JsonResponseHandler|Mock $jsonResponseHandler */
        $jsonResponseHandler = $this->createMock(JsonResponseHandler::class);
        $jsonResponseHandler->expects($this->once())
            ->method('setJsonApi')
            ->with(true);
        $jsonResponseHandler->expects($this->once())
            ->method('addTraceToOutput')
            ->with(true);
        /** @var Exception|Mock $exception */
        $exception = $this->createMock(Exception::class);

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
