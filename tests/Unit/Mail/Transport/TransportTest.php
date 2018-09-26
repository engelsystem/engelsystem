<?php

namespace Engelsystem\Test\Unit\Mail\Transport;

use Engelsystem\Test\Unit\Mail\Transport\Stub\TransportImplementation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swift_Mime_SimpleMessage as SimpleMessage;

class TransportTest extends TestCase
{
    /**
     * @covers \Engelsystem\Mail\Transport\Transport::isStarted
     * @covers \Engelsystem\Mail\Transport\Transport::ping
     */
    public function testMethods()
    {
        $transport = new TransportImplementation();

        $this->assertTrue($transport->isStarted());
        $this->assertTrue($transport->ping());
    }

    /**
     * @covers \Engelsystem\Mail\Transport\Transport::allRecipients
     */
    public function testAllRecipients()
    {
        /** @var SimpleMessage|MockObject $message */
        $message = $this->createMock(SimpleMessage::class);
        $transport = new TransportImplementation();
        $message->expects($this->once())
            ->method('getTo')
            ->willReturn([
                'foo@bar.batz'      => 'Foo Bar',
                'lorem@ipsum.dolor' => null,
            ]);
        $message->expects($this->once())
            ->method('getCc')
            ->willReturn([
                'to@bar.batz' => null,
            ]);
        $message->expects($this->once())
            ->method('getBcc')
            ->willReturn([
                'secret@bar.batz' => 'I\'m secret!',
            ]);

        $this->assertEquals(
            [
                'foo@bar.batz'      => 'Foo Bar',
                'lorem@ipsum.dolor' => null,
                'to@bar.batz'       => null,
                'secret@bar.batz'   => 'I\'m secret!',
            ],
            $transport->getAllRecipients($message)
        );
    }

    /**
     * @covers \Engelsystem\Mail\Transport\Transport::getTo
     * @covers \Engelsystem\Mail\Transport\Transport::formatTo
     */
    public function testGetTo()
    {
        /** @var SimpleMessage|MockObject $message */
        $message = $this->createMock(SimpleMessage::class);
        /** @var TransportImplementation|MockObject $transport */
        $transport = $this->getMockBuilder(TransportImplementation::class)
            ->setMethods(['allRecipients'])
            ->getMock();
        $transport->expects($this->once())
            ->method('allRecipients')
            ->with($message)
            ->willReturn([
                'foo@bar.batz'      => null,
                'lorem@ipsum.dolor' => 'Developer',
            ]);

        $return = $transport->getGetTo($message);
        $this->assertEquals('foo@bar.batz,Developer <lorem@ipsum.dolor>', $return);
    }
}
