<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Mail;

use Engelsystem\Mail\Mailer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;

class MailerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Mail\Mailer::__construct
     * @covers \Engelsystem\Mail\Mailer::getFromAddress
     * @covers \Engelsystem\Mail\Mailer::getFromName
     * @covers \Engelsystem\Mail\Mailer::setFromAddress
     * @covers \Engelsystem\Mail\Mailer::setFromName
     */
    public function testInitAndSettersAndGetters(): void
    {
        /** @var MailerInterface|MockObject $symfonyMailer */
        $symfonyMailer = $this->createMock(MailerInterface::class);

        $mailer = new Mailer($symfonyMailer);

        $mailer->setFromName('From Name');
        $this->assertEquals('From Name', $mailer->getFromName());

        $mailer->setFromAddress('from@foo.bar');
        $this->assertEquals('from@foo.bar', $mailer->getFromAddress());
    }

    /**
     * @covers \Engelsystem\Mail\Mailer::send
     */
    public function testSend(): void
    {
        /** @var MailerInterface|MockObject $symfonyMailer */
        $symfonyMailer = $this->createMock(MailerInterface::class);
        $symfonyMailer->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (RawMessage $message, Envelope $envelope = null): void {
                $this->assertStringContainsString('to@xam.pel', $message->toString());
                $this->assertStringContainsString('foo@bar.baz', $message->toString());
                $this->assertStringContainsString('Test Tester', $message->toString());
                $this->assertStringContainsString('Foo Bar', $message->toString());
                $this->assertStringContainsString('Lorem Ipsum!', $message->toString());
            });

        $mailer = new Mailer($symfonyMailer);
        $mailer->setFromAddress('foo@bar.baz');
        $mailer->setFromName('Test Tester');

        $mailer->send('to@xam.pel', 'Foo Bar', 'Lorem Ipsum!');
    }
}
