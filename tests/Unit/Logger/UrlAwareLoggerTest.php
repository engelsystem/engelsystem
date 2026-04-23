<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Logger;

use Engelsystem\Http\Request;
use Engelsystem\Logger\UrlAwareLogger;
use Engelsystem\Models\LogEntry;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use Psr\Log\LogLevel;

#[CoversMethod(UrlAwareLogger::class, 'createEntry')]
#[CoversMethod(UrlAwareLogger::class, 'setRequest')]
class UrlAwareLoggerTest extends TestCase
{
    use HasDatabase;

    public function testLog(): void
    {
        $this->initDatabase(); // To be able to run the test by itself

        $request = Request::create('https://localhost/err');

        $logger = new UrlAwareLogger(new LogEntry());

        $logger->log(LogLevel::INFO, 'Normal log');
        $logger->setRequest($request);
        $logger->log(LogLevel::INFO, 'URL log');

        $this->assertEquals(
            1,
            LogEntry::query()
                ->where('level', LogLevel::INFO)
                ->where('message', 'Normal log')
                ->whereNull('url')
                ->count()
        );
        $this->assertEquals(
            1,
            LogEntry::query()
                ->where('level', LogLevel::INFO)
                ->where('message', 'URL log')
                ->where('url', 'https://localhost/err')
                ->count()
        );
    }
}
