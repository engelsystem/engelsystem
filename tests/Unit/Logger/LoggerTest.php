<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Logger;

use Engelsystem\Logger\Logger;
use Engelsystem\Models\LogEntry;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\ServiceProviderTest;
use Exception;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use stdClass;

class LoggerTest extends ServiceProviderTest
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Logger\Logger::__construct
     */
    public function testImplements(): void
    {
        $this->assertInstanceOf(LoggerInterface::class, new Logger(new LogEntry()));
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
        $logger = new Logger(new LogEntry());

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
        $logger = new Logger(new LogEntry());

        $logger->log(LogLevel::INFO, 'My username is {username}', ['username' => 'Foo']);

        /** @var LogEntry $entry */
        $entry = LogEntry::find(1);
        $this->assertEquals('My username is Foo', $entry->message);
        $this->assertEquals(LogLevel::INFO, $entry['level']);
    }

    /**
     * @return array<string|array<string|mixed>>
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
        $logger = new Logger(new LogEntry());
        $logger->log(LogLevel::INFO, $message, $context);

        /** @var LogEntry $entry */
        $entry = LogEntry::find(1);
        $this->assertEquals($expected, $entry->message);
    }

    /**
     * @covers \Engelsystem\Logger\Logger::log
     */
    public function testContextToString(): void
    {
        $logger = new Logger(new LogEntry());

        $mock = $this->getMockBuilder(stdClass::class)
            ->addMethods(['__toString'])
            ->getMock();

        $mock->expects($this->atLeastOnce())
            ->method('__toString')
            ->will($this->returnValue('FooBar'));

        $logger->log(LogLevel::INFO, 'Some data and {context}', ['context' => $mock]);

        /** @var LogEntry $entry */
        $entry = LogEntry::find(1);
        $this->assertEquals('Some data and FooBar', $entry->message);
    }

    /**
     * @covers \Engelsystem\Logger\Logger::checkLevel
     * @covers \Engelsystem\Logger\Logger::log
     */
    public function testThrowExceptionOnInvalidLevel(): void
    {
        $logger = new Logger(new LogEntry());

        $this->expectException(InvalidArgumentException::class);
        $logger->log('This log level should never be defined', 'Some message');
    }

    /**
     * @covers \Engelsystem\Logger\Logger::formatException
     * @covers \Engelsystem\Logger\Logger::log
     */
    public function testWithException(): void
    {
        $logger = new Logger(new LogEntry());

        $logger->log(LogLevel::CRITICAL, 'Some random message', ['exception' => new Exception('Oops', 42)]);
        $line = __LINE__ - 1;
        /** @var LogEntry $entry */
        $entry = LogEntry::find(1);
        $this->assertStringContainsString('Some random message', $entry->message);
        $this->assertStringContainsString('Oops', $entry->message);
        $this->assertStringContainsString('42', $entry->message);
        $this->assertStringContainsString(__FILE__, $entry->message);
        $this->assertStringContainsString((string) $line, $entry->message);
        $this->assertStringContainsString(__FUNCTION__, $entry->message);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->initDatabase();
    }
}
