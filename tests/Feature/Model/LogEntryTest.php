<?php

namespace Engelsystem\Test\Feature\Model;

use Engelsystem\Models\LogEntry;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class LogEntryTest extends TestCase
{
    /**
     * @covers \Engelsystem\Models\LogEntry::filter
     */
    public function testFilter()
    {
        foreach ([
                     'Lorem Ipsum'            => LogLevel::INFO,
                     'Some test content'      => LogLevel::ERROR,
                     'Foo bar bartz!'         => LogLevel::INFO,
                     'Someone did something?' => LogLevel::NOTICE,
                     'This is a Test!'        => LogLevel::INFO,
                     'I\'m verbose notice!'   => LogLevel::DEBUG,
                     'The newest stuff!!'     => LogLevel::ERROR,
                 ] as $message => $level) {
            $entry = new LogEntry(['level' => $level, 'message' => $message]);
            $entry->save();
        }

        $model = new LogEntry();

        $return = $model->filter();
        $this->assertCount(7, $return);

        /** @var LogEntry $first */
        $first = $return->first();

        $this->assertEquals('The newest stuff!!', $first->message);

        $return = $model->filter(LogLevel::INFO);
        $this->assertCount(3, $return);

        $return = $model->filter('notice');
        $this->assertCount(2, $return);

        $return = $model->filter('bartz');
        $this->assertCount(1, $return);
    }

    /**
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        LogEntry::query()->truncate();
    }
}
