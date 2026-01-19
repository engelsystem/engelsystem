<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Logger;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Logger\UserAwareLogger;
use Engelsystem\Models\LogEntry;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use Psr\Log\LogLevel;

#[CoversMethod(UserAwareLogger::class, 'createEntry')]
#[CoversMethod(UserAwareLogger::class, 'setAuth')]
class UserAwareLoggerTest extends TestCase
{
    use HasDatabase;

    public function testLog(): void
    {
        $this->initDatabase(); // To be able to run the test by itself

        $user = User::factory(['id' => 1, 'name' => 'admin'])->make();

        $logEntry = $this->getMockBuilder(LogEntry::class)
            ->onlyMethods(['__call'])
            ->getMock();
        $matcher = $this->exactly(2);
        $logEntry->expects($matcher)
            ->method('__call')->willReturnCallback(function ($method, $parameters) use ($matcher): void {
                $this->assertSame('create', $method);
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame([
                        'level' => LogLevel::INFO,
                        'message' => 'Some more informational foo',
                    ], $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame([
                        'level' => LogLevel::INFO,
                        'message' => 'Some even more informational bar', 'user_id' => 1,
                    ], $parameters[0]);
                }
            });

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
