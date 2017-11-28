<?php

namespace Engelsystem\Test\Unit\Exceptions\handlers;


use Engelsystem\Exceptions\Handlers\Legacy;
use Engelsystem\Http\Request;
use Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class LegacyTest extends TestCase
{
    /**
     * @covers \Engelsystem\Exceptions\Handlers\Legacy::render()
     */
    public function testRender()
    {
        $handler = new Legacy();
        /** @var Request|Mock $request */
        $request = $this->createMock(Request::class);
        /** @var Exception|Mock $exception */
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

        $this->assertContains('4242', $logContent);
        $this->assertContains('Lorem Ipsum', $logContent);
        $this->assertContains(basename(__FILE__), $logContent);
        $this->assertContains((string)$line, $logContent);
        $this->assertContains(__FUNCTION__, $logContent);
        $this->assertContains(json_encode(__CLASS__), $logContent);
    }
}
