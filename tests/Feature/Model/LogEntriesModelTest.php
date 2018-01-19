<?php

namespace Engelsystem\Test\Feature\Model;

use Engelsystem\Test\Feature\ApplicationFeatureTest;
use Psr\Log\LogLevel;

class LogEntriesModelTest extends ApplicationFeatureTest
{
    public function testCreateLogEntry()
    {
        LogEntries_clear_all();
        $count = count(LogEntries());
        $this->assertNotFalse(LogEntry_create(LogLevel::WARNING, 'test_LogEntry_create'));

        // There should be one more log entry now
        $this->assertEquals(count(LogEntries()), $count + 1);
    }

    public function testClearAllLogEntries()
    {
        LogEntry_create(LogLevel::WARNING, 'test');
        $this->assertTrue(count(LogEntries()) > 0);

        $this->assertNotFalse(LogEntries_clear_all());
        $this->assertCount(0, LogEntries());
    }

    public function tearDown()
    {
        LogEntries_clear_all();
    }
}
