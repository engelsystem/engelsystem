<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Mail\Transport;

use Engelsystem\Mail\Transport\LogTransport;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;
use Symfony\Component\Mime\Email;

class LogTransportTest extends TestCase
{
    /**
     * @covers \Engelsystem\Mail\Transport\LogTransport::__construct
     * @covers \Engelsystem\Mail\Transport\LogTransport::doSend
     */
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

    /**
     * @covers \Engelsystem\Mail\Transport\LogTransport::__toString
     */
    public function testToString(): void
    {
        /** @var LoggerInterface|MockObject $logger */
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $transport = new LogTransport($logger);
        $this->assertEquals('log://', (string) $transport);
    }
}
