<?php

namespace Engelsystem\Test\Unit\Exceptions\handlers;

use Engelsystem\Exceptions\Handlers\Legacy;
use Engelsystem\Http\Request;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LegacyTest extends TestCase
{
    /**
     * @covers \Engelsystem\Exceptions\Handlers\Legacy::render()
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
     * @covers \Engelsystem\Exceptions\Handlers\Legacy::report()
     * @covers \Engelsystem\Exceptions\Handlers\Legacy::stripBasePath()
     */
    public function testReport()
    {
        $handler = new Legacy();
        $exception = new Exception('Lorem Ipsum', 4242);
        $line = __LINE__ - 1;

        $log = tempnam(sys_get_temp_dir(), 'engelsystem-log');
        $errorLog = ini_get('error_log');
        ini_set('error_log', $log);
        $handler->report($exception);
        ini_set('error_log', $errorLog);
        $logContent = file_get_contents($log);
        unset($log);

        $this->assertStringContainsString('4242', $logContent);
        $this->assertStringContainsString('Lorem Ipsum', $logContent);
        $this->assertStringContainsString(basename(__FILE__), $logContent);
        $this->assertStringContainsString((string)$line, $logContent);
        $this->assertStringContainsString(__FUNCTION__, $logContent);
        $this->assertStringContainsString(json_encode(__CLASS__), $logContent);
    }
}
