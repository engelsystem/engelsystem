<?php

namespace Engelsystem\Test\Unit\Logger;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Logger\UserAwareLogger;
use Engelsystem\Models\LogEntry;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;

class UserAwareLoggerTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Logger\UserAwareLogger::log
     * @covers \Engelsystem\Logger\UserAwareLogger::setAuth
     */
    public function testLog()
    {
        $user = User::factory(['id' => 1, 'name' => 'admin'])->make();

        /** @var LogEntry|MockObject $logEntry */
        $logEntry = $this->getMockBuilder(LogEntry::class)
            ->addMethods(['create'])
            ->getMock();
        $logEntry->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive(
                [['level' => LogLevel::INFO, 'message' => 'Some more informational foo']],
                [['level' => LogLevel::INFO, 'message' => 'admin (1): Some even more informational bar']]
            );

        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        $auth->expects($this->exactly(2))
            ->method('user')
            ->willReturnOnConsecutiveCalls(
                null,
                $user
            );

        $logger = new UserAwareLogger($logEntry);
        $logger->setAuth($auth);

        $logger->log(LogLevel::INFO, 'Some more informational foo');
        $logger->log(LogLevel::INFO, 'Some even more informational bar');
    }
}
