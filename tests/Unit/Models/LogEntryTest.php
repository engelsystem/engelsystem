<?php

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\LogEntry;
use Psr\Log\LogLevel;

class LogEntryTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\LogEntry::filter
     */
    public function testFilter(): void
    {
        foreach (
            [
                'I\'m an info'            => LogLevel::INFO,
                '*Insert explosion here*' => LogLevel::EMERGENCY,
                'Tracing along'           => LogLevel::DEBUG,
                'Oops'                    => LogLevel::ERROR,
                'It\'s happening'         => LogLevel::INFO,
                'Something is wrong'      => LogLevel::ERROR,
                'Ohi'                     => LogLevel::INFO,
            ] as $message => $level
        ) {
            (new LogEntry(['level' => $level, 'message' => $message]))->save();
        }

        $this->assertCount(7, LogEntry::filter());
        $this->assertCount(3, LogEntry::filter(LogLevel::INFO));
        $this->assertCount(1, LogEntry::filter('Oops'));
    }
}
