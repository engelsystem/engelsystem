<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Mail;

use Engelsystem\Mail\Mailer;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Psr\Log\Test\TestLogger;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;

#[CoversMethod(Mailer::class, '__construct')]
#[CoversMethod(Mailer::class, 'getFromAddress')]
#[CoversMethod(Mailer::class, 'getFromName')]
#[CoversMethod(Mailer::class, 'setFromAddress')]
#[CoversMethod(Mailer::class, 'setFromName')]
#[CoversMethod(Mailer::class, 'send')]
class MailerTest extends TestCase
{
    public function testInitAndSettersAndGetters(): void
    {
        $log = new NullLogger();
        $symfonyMailer = $this->createStub(MailerInterface::class);

        $mailer = new Mailer($log, $symfonyMailer);

        $mailer->setFromName('From Name');
        $this->assertEquals('From Name', $mailer->getFromName());

        $mailer->setFromAddress('from@foo.bar');
        $this->assertEquals('from@foo.bar', $mailer->getFromAddress());
    }

    public function testSend(): void
    {
        $log = new NullLogger();
        $symfonyMailer = $this->createMock(MailerInterface::class);
        $symfonyMailer->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (RawMessage $message, ?Envelope $envelope = null): void {
                $this->assertStringContainsString('to@xam.pel', $message->toString());
                $this->assertStringContainsString('foo@bar.baz', $message->toString());
                $this->assertStringContainsString('Test Tester', $message->toString());
                $this->assertStringContainsString('Foo Bar', $message->toString());
                $this->assertStringContainsString('Lorem Ipsum!', $message->toString());
            });

        $mailer = new Mailer($log, $symfonyMailer);
        $mailer->setFromAddress('foo@bar.baz');
        $mailer->setFromName('Test Tester');

        $status = $mailer->send('to@xam.pel', 'Foo Bar', 'Lorem Ipsum!');
        $this->assertTrue($status);
    }


    public function testSendException(): void
    {
        $log = new TestLogger();
        $symfonyMailer = $this->createMock(MailerInterface::class);
        $symfonyMailer->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (RawMessage $message, ?Envelope $envelope = null): void {
                throw new TransportException('Unable to connect to port 42');
            });

        $mailer = new Mailer($log, $symfonyMailer);
        $mailer->setFromAddress('foo@bar.baz');
        $mailer->setFromName('Test Tester');

        $status = $mailer->send('to@xam.pel', 'Foo Bar', 'Lorem Ipsum!');
        $this->assertFalse($status);

        $this->assertTrue($log->hasErrorThatContains('Unable to send e-mail'));
    }
}
