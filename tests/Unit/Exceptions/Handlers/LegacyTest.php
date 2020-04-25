<?php

namespace Engelsystem\Test\Unit\Exceptions\Handlers;

use Engelsystem\Exceptions\Handlers\Legacy;
use Engelsystem\Http\Request;
use ErrorException;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

class LegacyTest extends TestCase
{
    /**
     * @covers \Engelsystem\Exceptions\Handlers\Legacy::render
     */
    public function testRender()
    {
        $handler = new Legacy();
        /** @var Request|MockObject $request */
        $request = $this->createMock(Request::class);
        /** @var Exception|MockObject $exception */
        $exception = $this->createMock(Exception::class);

        $this->expectOutputRegex('/.*error occurred.*/i');

        $handler->render($request, $exception);
    }

    /**
     * @covers \Engelsystem\Exceptions\Handlers\Legacy::report
     * @covers \Engelsystem\Exceptions\Handlers\Legacy::setLogger
     * @covers \Engelsystem\Exceptions\Handlers\Legacy::stripBasePath
     */
    public function testReport()
    {
        $handler = new Legacy();
        $exception = new Exception('Lorem Ipsum', 4242);
        $line = __LINE__ - 1;
        $exception2 = new Exception('Test Exception');
        $exception3 = new Exception('Mor Exceptions!');
        $logger = new TestLogger();
        $logger2 = $this->createMock(TestLogger::class);
        $logger2->expects($this->once())
            ->method('critical')
            ->willReturnCallback(function () {
                throw new ErrorException();
            });

        $logfile = tempnam(sys_get_temp_dir(), 'engelsystem-log');
        $errorLog = ini_get('error_log');
        ini_set('error_log', $logfile);
        $handler->report($exception);
        $handler->setLogger($logger);
        $handler->report($exception2);
        $handler->setLogger($logger2);
        $handler->report($exception3);
        ini_set('error_log', $errorLog);
        $logContent = file_get_contents($logfile);
        unset($logfile);

        $this->assertStringContainsString('4242', $logContent);
        $this->assertStringContainsString('Lorem Ipsum', $logContent);
        $this->assertStringContainsString(basename(__FILE__), $logContent);
        $this->assertStringContainsString((string)$line, $logContent);
        $this->assertStringContainsString(__FUNCTION__, $logContent);
        $this->assertStringContainsString(json_encode(__CLASS__), $logContent);

        $this->assertTrue($logger->hasRecordThatPasses(function (array $record) use ($exception2) {
            $context = $record['context'];
            return isset($context['exception']) && $context['exception'] === $exception2;
        }, 'critical'));
    }
}
