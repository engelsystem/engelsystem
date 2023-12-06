<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Logger;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Logger\UserAwareLogger;
use Engelsystem\Models\LogEntry;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;

class UserAwareLoggerTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Logger\UserAwareLogger::createEntry
     * @covers \Engelsystem\Logger\UserAwareLogger::setAuth
     */
    public function testLog(): void
    {
        $this->initDatabase(); // To be able to run the test by itself

        $user = User::factory(['id' => 1, 'name' => 'admin'])->make();

        /** @var LogEntry|MockObject $logEntry */
        $logEntry = $this->getMockBuilder(LogEntry::class)
            ->addMethods(['create'])
            ->getMock();
        $logEntry->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive(
                [['level' => LogLevel::INFO, 'message' => 'Some more informational foo']],
                [['level' => LogLevel::INFO, 'message' => 'Some even more informational bar', 'user_id' => 1]]
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
