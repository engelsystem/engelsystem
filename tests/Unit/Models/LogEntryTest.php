<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\LogEntry;
use Engelsystem\Models\User\User;
use Psr\Log\LogLevel;

class LogEntryTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\LogEntry::filter
     */
    public function testFilter(): void
    {
        $user = User::factory()->create();
        (new LogEntry(['level' => LogLevel::DEBUG, 'message' => 'Some users fault', 'user_id' => $user->id]))->save();
        foreach (
            [
                'I\'m an info'            => LogLevel::INFO,
                '*Insert explosion here*' => LogLevel::EMERGENCY,
                'Tracing along'           => LogLevel::DEBUG,
                'Oops, no notice given'   => LogLevel::NOTICE,
                'It\'s happening'         => LogLevel::INFO,
                'Something is wrong'      => LogLevel::ERROR,
                'Ohi'                     => LogLevel::INFO,
                'I\'m no notice'          => LogLevel::CRITICAL,
                'Just here to warn you!'  => LogLevel::WARNING,
                'The newest stuff!!'      => LogLevel::ALERT,
            ] as $message => $level
        ) {
            (new LogEntry(['level' => $level, 'message' => $message]))->save();
        }

        $this->assertCount(11, LogEntry::filter());
        $this->assertCount(3, LogEntry::filter(null, null, LogLevel::INFO));
        $this->assertCount(1, LogEntry::filter('Oops'));
        $this->assertCount(1, LogEntry::filter(null, $user->id));

        /** @var LogEntry $first */
        $first = LogEntry::filter()->first();
        $this->assertEquals('The newest stuff!!', $first->message);

        $return = LogEntry::filter('notice');
        $this->assertCount(2, $return);

        $return = LogEntry::filter('Ohi');
        $this->assertCount(1, $return);
    }
}
