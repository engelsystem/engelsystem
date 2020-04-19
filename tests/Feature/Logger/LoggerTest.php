<?php

namespace Engelsystem\Test\Feature\Logger;

use Engelsystem\Logger\Logger;
use Engelsystem\Models\LogEntry;
use Engelsystem\Test\Feature\ApplicationFeatureTest;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use stdClass;

class LoggerTest extends ApplicationFeatureTest
{
    /**
     * @covers \Engelsystem\Logger\Logger::__construct
     * @return LoggerInterface
     */
    public function getLogger()
    {
        $logEntry = new LogEntry();
        return new Logger($logEntry);
    }

    /**
     * @covers \Engelsystem\Logger\Logger::__construct
     */
    public function testImplements()
    {
        $this->assertInstanceOf(LoggerInterface::class, $this->getLogger());
    }

    /**
     * @return string[][]
     */
    public function provideLogLevels()
    {
        return [
            [LogLevel::ALERT],
            [LogLevel::CRITICAL],
            [LogLevel::DEBUG],
            [LogLevel::EMERGENCY],
            [LogLevel::ERROR],
            [LogLevel::INFO],
            [LogLevel::NOTICE],
            [LogLevel::WARNING],
        ];
    }

    /**
     * @covers       \Engelsystem\Logger\Logger::log
     * @dataProvider provideLogLevels
     * @param string $level
     */
    public function testAllLevels($level)
    {
        LogEntry::query()->truncate();
        $logger = $this->getLogger();

        $logger->log($level, 'First log message');
        $logger->{$level}('Second log message');

        $entries = LogEntry::all();
        $this->assertCount(2, $entries);
    }

    /**
     * @covers \Engelsystem\Logger\Logger::log
     */
    public function testContextReplacement()
    {
        LogEntry::query()->truncate();
        $logger = $this->getLogger();

        $logger->log(LogLevel::INFO, 'My username is {username}', ['username' => 'Foo']);

        $entry = $this->getLastEntry();
        $this->assertEquals('My username is Foo', $entry['message']);
        $this->assertEquals(LogLevel::INFO, $entry['level']);
    }

    /**
     * @return mixed[][]
     */
    public function provideContextReplaceValues()
    {
        return [
            ['Data and {context}', [], 'Data and {context}'],
            ['Data and {context}', ['context' => null], 'Data and '],
            ['Data and {context}', ['context' => new stdClass()], 'Data and {context}'],
            ['Some user asked: {question}', ['question' => 'Foo?'], 'Some user asked: Foo?'],
        ];
    }

    /**
     * @covers       \Engelsystem\Logger\Logger::interpolate
     * @covers       \Engelsystem\Logger\Logger::log
     * @dataProvider provideContextReplaceValues
     *
     * @param string   $message
     * @param string[] $context
     * @param string   $expected
     */
    public function testContextReplaceValues($message, $context, $expected)
    {
        $logger = $this->getLogger();
        $logger->log(LogLevel::INFO, $message, $context);

        $entry = $this->getLastEntry();
        $this->assertEquals($expected, $entry['message']);
    }

    /**
     * @covers \Engelsystem\Logger\Logger::log
     */
    public function testContextToString()
    {
        LogEntry::query()->truncate();
        $logger = $this->getLogger();

        $mock = $this->getMockBuilder(stdClass::class)
            ->addMethods(['__toString'])
            ->getMock();

        $mock->expects($this->atLeastOnce())
            ->method('__toString')
            ->will($this->returnValue('FooBar'));

        $logger->log(LogLevel::INFO, 'Some data and {context}', ['context' => $mock]);

        $entry = $this->getLastEntry();
        $this->assertEquals('Some data and FooBar', $entry['message']);
    }

    /**
     * @covers \Engelsystem\Logger\Logger::checkLevel
     * @covers \Engelsystem\Logger\Logger::log
     */
    public function testThrowExceptionOnInvalidLevel()
    {
        $logger = $this->getLogger();

        $this->expectException(InvalidArgumentException::class);
        $logger->log('This log level should never be defined', 'Some message');
    }

    /**
     * @return array
     */
    protected function getLastEntry()
    {
        return LogEntry::all()->last();
    }

    /**
     * Cleanup
     */
    protected function tearDown(): void
    {
        LogEntry::query()->truncate();
    }
}
