<?php

namespace Engelsystem\Test\Unit\Mail;

use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Renderer\Renderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swift_Mailer as SwiftMailer;

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
}
