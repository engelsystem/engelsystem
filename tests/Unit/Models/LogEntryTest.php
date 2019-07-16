<?php

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\LogEntry;
use Engelsystem\Test\Unit\HasDatabase;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class LogEntryTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Models\LogEntry::filter
     */
    public function testFilter()
    {
        foreach ([
                     'I\'m an info'            => LogLevel::INFO,
                     '*Insert explosion here*' => LogLevel::EMERGENCY,
                     'Tracing along'           => LogLevel::DEBUG,
                     'Oops'                    => LogLevel::ERROR,
                     'It\'s happening'         => LogLevel::INFO,
                     'Something is wrong'      => LogLevel::ERROR,
                     'Ohi'                     => LogLevel::INFO,
                 ] as $message => $level) {
            (new LogEntry(['level' => $level, 'message' => $message]))->save();
        }

        $this->assertCount(7, LogEntry::filter());
        $this->assertCount(3, LogEntry::filter(LogLevel::INFO));
        $this->assertCount(1, LogEntry::filter('Oops'));
    }

    /**
     * Prepare test
     */
    protected function setUp(): void
    {
        $this->initDatabase();
    }
}
