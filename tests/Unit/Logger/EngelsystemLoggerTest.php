<?php

namespace Engelsystem\Test\Unit\Logger;

use Engelsystem\Logger\EngelsystemLogger;
use Engelsystem\Models\LogEntry;
use Engelsystem\Test\Unit\ServiceProviderTest;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;

class EngelsystemLoggerTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Logger\EngelsystemLogger::__construct
     * @covers \Engelsystem\Logger\EngelsystemLogger::log
     */
    public function testLog()
    {
        /** @var LogEntry|MockObject $logEntry */
        $logEntry = $this->createMock(LogEntry::class);
        $logEntry->expects($this->once())
            ->method('create')
            ->with(['level' => LogLevel::INFO, 'message' => 'I\'m an information!']);

        $logger = new EngelsystemLogger($logEntry);

        $logger->log(LogLevel::INFO, 'I\'m an information!');
    }

    /**
     * @covers \Engelsystem\Logger\EngelsystemLogger::log
     * @covers \Engelsystem\Logger\EngelsystemLogger::checkLevel
     */
    public function testCheckLevel()
    {
        /** @var LogEntry|MockObject $logEntry */
        $logEntry = $this->createMock(LogEntry::class);
        $logger = new EngelsystemLogger($logEntry);

        $this->expectException(InvalidArgumentException::class);
        $logger->log('FooBar', 'Random Stuff');
    }

    /**
     * @covers \Engelsystem\Logger\EngelsystemLogger::interpolate
     */
    public function testInterpolate()
    {
        /** @var LogEntry|MockObject $logEntry */
        $logEntry = $this->createMock(LogEntry::class);
        $logEntry->expects($this->exactly(3))
            ->method('create')
            ->withConsecutive(
                [['level' => LogLevel::DEBUG, 'message' => 'User: Foo']],
                [['level' => LogLevel::NOTICE, 'message' => 'User: {user}']],
                [['level' => LogLevel::NOTICE, 'message' => 'User: Bar']]
            );

        $logger = new EngelsystemLogger($logEntry);

        $logger->log(LogLevel::DEBUG, 'User: {user}', ['user' => 'Foo']);
        $logger->log(LogLevel::NOTICE, 'User: {user}', ['user' => ['name' => 'Lorem']]);
        $logger->log(LogLevel::NOTICE, 'User: {user}', [
            'user' =>
                new class
                {
                    public function __toString() { return 'Bar'; }
                }
        ]);
    }
}
