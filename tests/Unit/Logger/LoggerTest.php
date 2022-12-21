<?php

namespace Engelsystem\Test\Unit\Logger;

use Engelsystem\Logger\Logger;
use Engelsystem\Models\LogEntry;
use Engelsystem\Test\Unit\ServiceProviderTest;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;

class LoggerTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Logger\Logger::__construct
     * @covers \Engelsystem\Logger\Logger::log
     */
    public function testLog(): void
    {
        /** @var LogEntry|MockObject $logEntry */
        $logEntry = $this->getMockBuilder(LogEntry::class)
            ->addMethods(['create'])
            ->getMock();
        $logEntry->expects($this->once())
            ->method('create')
            ->with(['level' => LogLevel::INFO, 'message' => 'I\'m an information!']);

        $logger = new Logger($logEntry);

        $logger->log(LogLevel::INFO, 'I\'m an information!');
    }

    /**
     * @covers \Engelsystem\Logger\Logger::log
     * @covers \Engelsystem\Logger\Logger::checkLevel
     */
    public function testCheckLevel(): void
    {
        /** @var LogEntry|MockObject $logEntry */
        $logEntry = $this->createMock(LogEntry::class);
        $logger = new Logger($logEntry);

        $this->expectException(InvalidArgumentException::class);
        $logger->log('FooBar', 'Random Stuff');
    }

    /**
     * @covers \Engelsystem\Logger\Logger::interpolate
     */
    public function testInterpolate(): void
    {
        /** @var LogEntry|MockObject $logEntry */
        $logEntry = $this->getMockBuilder(LogEntry::class)
            ->addMethods(['create'])
            ->getMock();
        $logEntry->expects($this->exactly(3))
            ->method('create')
            ->withConsecutive(
                [['level' => LogLevel::DEBUG, 'message' => 'User: Foo']],
                [['level' => LogLevel::NOTICE, 'message' => 'User: {user}']],
                [['level' => LogLevel::NOTICE, 'message' => 'User: Bar']]
            );

        $logger = new Logger($logEntry);

        $logger->log(LogLevel::DEBUG, 'User: {user}', ['user' => 'Foo']);
        $logger->log(LogLevel::NOTICE, 'User: {user}', ['user' => ['name' => 'Lorem']]);
        $logger->log(LogLevel::NOTICE, 'User: {user}', [
            'user' =>
                new class
                {
                    public function __toString(): string
                    {
                        return 'Bar';
                    }
                }
        ]);
    }
}
