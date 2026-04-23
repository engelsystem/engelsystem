<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Logger;

use Engelsystem\Http\Request;
use Engelsystem\Logger\UrlAwareLogger;
use Engelsystem\Models\LogEntry;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;

class UrlAwareLoggerTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Logger\UrlAwareLogger::createEntry
     * @covers \Engelsystem\Logger\UrlAwareLogger::setRequest
     */
    public function testLog(): void
    {
        $this->initDatabase(); // To be able to run the test by itself

        $request = Request::create('https://localhost/err');

        /** @var LogEntry|MockObject $logEntry */
        $logEntry = $this->getMockBuilder(LogEntry::class)
            ->addMethods(['create'])
            ->getMock();
        $logEntry->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive(
                [['level' => LogLevel::INFO, 'message' => 'Normal log']],
                [['level' => LogLevel::INFO, 'message' => 'URL log', 'url' => 'https://localhost/err']]
            );

        $logger = new UrlAwareLogger($logEntry);

        $logger->log(LogLevel::INFO, 'Normal log');
        $logger->setRequest($request);
        $logger->log(LogLevel::INFO, 'URL log');
    }
}
