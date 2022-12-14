<?php

namespace Engelsystem\Test\Feature\Logger;

use Engelsystem\Logger\Logger;
use Engelsystem\Models\LogEntry;
use Engelsystem\Test\Feature\ApplicationFeatureTest;
use Exception;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use stdClass;

class LoggerTest extends ApplicationFeatureTest
{
    /**
     * @covers \Engelsystem\Logger\Logger::__construct
     */
    public function getLogger(): LoggerInterface
    {
        $logEntry = new LogEntry();
        return new Logger($logEntry);
    }

    /**
     * @covers \Engelsystem\Logger\Logger::__construct
     */
    public function testImplements(): void
    {
        $this->assertInstanceOf(LoggerInterface::class, $this->getLogger());
    }

    /**
     * @return string[][]
     */
    public function provideLogLevels(): array
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
     */
    public function testAllLevels(string $level): void
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
    public function testContextReplacement(): void
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
    public function provideContextReplaceValues(): array
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
     * @param string[] $context
     */
    public function testContextReplaceValues(string $message, array $context, string $expected): void
    {
        $logger = $this->getLogger();
        $logger->log(LogLevel::INFO, $message, $context);

        $entry = $this->getLastEntry();
        $this->assertEquals($expected, $entry['message']);
    }

    /**
     * @covers \Engelsystem\Logger\Logger::log
     */
    public function testContextToString(): void
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
    public function testThrowExceptionOnInvalidLevel(): void
    {
        $logger = $this->getLogger();

        $this->expectException(InvalidArgumentException::class);
        $logger->log('This log level should never be defined', 'Some message');
    }

    /**
     * @covers \Engelsystem\Logger\Logger::formatException
     * @covers \Engelsystem\Logger\Logger::log
     */
    public function testWithException(): void
    {
        $logger = $this->getLogger();

        $logger->log(LogLevel::CRITICAL, 'Some random message', ['exception' => new Exception('Oops', 42)]);
        $line = __LINE__ - 1;

        $entry = $this->getLastEntry();
        $this->assertStringContainsString('Some random message', $entry['message']);
        $this->assertStringContainsString('Oops', $entry['message']);
        $this->assertStringContainsString('42', $entry['message']);
        $this->assertStringContainsString(__FILE__, $entry['message']);
        $this->assertStringContainsString((string)$line, $entry['message']);
        $this->assertStringContainsString(__FUNCTION__, $entry['message']);
    }

    protected function getLastEntry(): LogEntry
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
