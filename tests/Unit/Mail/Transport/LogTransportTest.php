<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Mail\Transport;

use Engelsystem\Mail\Transport\LogTransport;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;
use Symfony\Component\Mime\Email;

#[CoversMethod(LogTransport::class, '__construct')]
#[CoversMethod(LogTransport::class, 'doSend')]
#[CoversMethod(LogTransport::class, '__toString')]
class LogTransportTest extends TestCase
{
    public function testSend(): void
    {
        $logger = new TestLogger();
        $email = (new Email())
            ->from('some@email.host')
            ->to('foo@bar.baz', 'Test Tester <test@example.local>')
            ->subject('Testing')
            ->text('Message body');

        $transport = new LogTransport($logger);
        $transport->send($email);

        $this->assertTrue($logger->hasDebugThatContains('Send mail to'));
    }

    public function testToString(): void
    {
        $logger = $this->getStubBuilder(LoggerInterface::class)->getStub();

        $transport = new LogTransport($logger);
        $this->assertEquals('log://', (string) $transport);
    }
}
