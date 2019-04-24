<?php

namespace Engelsystem\Test\Unit\Mail\Transport;

use Engelsystem\Mail\Transport\LogTransport;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Swift_Mime_SimpleMessage as SimpleMessage;

class LogTransportTest extends TestCase
{
    /**
     * @covers \Engelsystem\Mail\Transport\LogTransport::__construct
     * @covers \Engelsystem\Mail\Transport\LogTransport::send
     */
    public function testSend()
    {
        /** @var LoggerInterface|MockObject $logger */
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        /** @var SimpleMessage|MockObject $message */
        $message = $this->createMock(SimpleMessage::class);

        $message->expects($this->once())
            ->method('getSubject')
            ->willReturn('Some subject');
        $message->expects($this->once())
            ->method('getHeaders')
            ->willReturn('Head: er');
        $message->expects($this->once())
            ->method('toString')
            ->willReturn('Message body');

        $logger->expects($this->once())
            ->method('debug')
            ->willReturnCallback(function ($message, $context = []) {
                foreach (array_keys($context) as $key) {
                    $this->assertStringContainsString(sprintf('{%s}', $key), $message);
                }

                $this->assertEquals('Some subject', $context['title']);
                $this->assertEquals('foo@bar.batz,Lorem Ipsum <lor@em.ips>', $context['recipients']);
                $this->assertStringContainsString('Head: er', $context['content']);
                $this->assertStringContainsString('Message body', $context['content']);
            });

        /** @var LogTransport|MockObject $transport */
        $transport = $this->getMockBuilder(LogTransport::class)
            ->setConstructorArgs(['logger' => $logger])
            ->setMethods(['allRecipients'])
            ->getMock();
        $transport->expects($this->exactly(2))
            ->method('allRecipients')
            ->with($message)
            ->willReturn(['foo@bar.batz' => null, 'lor@em.ips' => 'Lorem Ipsum']);

        $return = $transport->send($message);
        $this->equalTo(2, $return);
    }
}
