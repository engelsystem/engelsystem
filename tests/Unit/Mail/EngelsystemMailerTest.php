<?php

namespace Engelsystem\Test\Unit\Mail;

use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Renderer\Renderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swift_Mailer as SwiftMailer;
use Swift_Message as SwiftMessage;

class EngelsystemMailerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Mail\EngelsystemMailer::__construct
     * @covers \Engelsystem\Mail\EngelsystemMailer::sendView
     */
    public function testSendView()
    {
        /** @var Renderer|MockObject $view */
        $view = $this->createMock(Renderer::class);
        /** @var SwiftMailer|MockObject $swiftMailer */
        $swiftMailer = $this->createMock(SwiftMailer::class);
        /** @var EngelsystemMailer|MockObject $mailer */
        $mailer = $this->getMockBuilder(EngelsystemMailer::class)
            ->setConstructorArgs(['mailer' => $swiftMailer, 'view' => $view])
            ->setMethods(['send'])
            ->getMock();
        $mailer->expects($this->once())
            ->method('send')
            ->with('foo@bar.baz', 'Lorem dolor', 'Rendered Stuff!')
            ->willReturn(1);
        $view->expects($this->once())
            ->method('render')
            ->with('test/template.tpl', ['dev' => true])
            ->willReturn('Rendered Stuff!');

        $return = $mailer->sendView('foo@bar.baz', 'Lorem dolor', 'test/template.tpl', ['dev' => true]);
        $this->equalTo(1, $return);
    }

    /**
     * @covers \Engelsystem\Mail\EngelsystemMailer::send
     * @covers \Engelsystem\Mail\EngelsystemMailer::setSubjectPrefix
     * @covers \Engelsystem\Mail\EngelsystemMailer::getSubjectPrefix
     */
    public function testSend()
    {
        /** @var SwiftMessage|MockObject $message */
        $message = $this->createMock(SwiftMessage::class);
        /** @var SwiftMailer|MockObject $swiftMailer */
        $swiftMailer = $this->createMock(SwiftMailer::class);
        $swiftMailer->expects($this->once())
            ->method('createMessage')
            ->willReturn($message);
        $swiftMailer->expects($this->once())
            ->method('send')
            ->willReturn(1);

        $message->expects($this->once())
            ->method('setTo')
            ->with(['to@xam.pel'])
            ->willReturn($message);

        $message->expects($this->once())
            ->method('setFrom')
            ->with('foo@bar.baz', 'Lorem Ipsum')
            ->willReturn($message);

        $message->expects($this->once())
            ->method('setSubject')
            ->with('[Mail test] Foo Bar')
            ->willReturn($message);

        $message->expects($this->once())
            ->method('setBody')
            ->with('Lorem Ipsum!')
            ->willReturn($message);

        $mailer = new EngelsystemMailer($swiftMailer);
        $mailer->setFromAddress('foo@bar.baz');
        $mailer->setFromName('Lorem Ipsum');
        $mailer->setSubjectPrefix('Mail test');

        $this->assertEquals('Mail test', $mailer->getSubjectPrefix());

        $return = $mailer->send('to@xam.pel', 'Foo Bar', 'Lorem Ipsum!');
        $this->equalTo(1, $return);
    }
}
