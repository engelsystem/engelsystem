<?php

namespace Engelsystem\Test\Unit\Mail;

use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\User\Contact;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\User;
use Engelsystem\Renderer\Renderer;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Swift_Mailer as SwiftMailer;
use Swift_Message as SwiftMessage;

class EngelsystemMailerTest extends TestCase
{
    use HasDatabase;

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
            ->onlyMethods(['send'])
            ->getMock();
        $this->setExpects($mailer, 'send', ['foo@bar.baz', 'Lorem dolor', 'Rendered Stuff!'], 1);
        $this->setExpects($view, 'render', ['test/template.tpl', ['dev' => true]], 'Rendered Stuff!');

        $return = $mailer->sendView('foo@bar.baz', 'Lorem dolor', 'test/template.tpl', ['dev' => true]);
        $this->assertEquals(1, $return);
    }

    /**
     * @covers \Engelsystem\Mail\EngelsystemMailer::sendViewTranslated
     */
    public function testSendViewTranslated()
    {
        $this->initDatabase();

        $user = User::factory(['email' => 'foo@bar.baz'])
            ->has(Settings::factory(['language' => 'de_DE']))
            ->has(Contact::factory(['email' => null]))
            ->create();

        /** @var Renderer|MockObject $view */
        $view = $this->createMock(Renderer::class);
        /** @var SwiftMailer|MockObject $swiftMailer */
        $swiftMailer = $this->createMock(SwiftMailer::class);
        /** @var Translator|MockObject $translator */
        $translator = $this->createMock(Translator::class);

        /** @var EngelsystemMailer|MockObject $mailer */
        $mailer = $this->getMockBuilder(EngelsystemMailer::class)
            ->setConstructorArgs(['mailer' => $swiftMailer, 'view' => $view, 'translation' => $translator])
            ->onlyMethods(['sendView'])
            ->getMock();

        $this->setExpects($mailer, 'sendView', ['foo@bar.baz', 'Lorem dolor', 'test/template.tpl', ['dev' => true]], 1);
        $this->setExpects($translator, 'getLocales', null, ['de_DE' => 'de_DE', 'en_US' => 'en_US']);
        $this->setExpects($translator, 'getLocale', null, 'en_US');
        $this->setExpects($translator, 'translate', ['translatable.text', ['dev' => true]], 'Lorem dolor');
        $translator->expects($this->exactly(2))
            ->method('setLocale')
            ->withConsecutive(['de_DE'], ['en_US']);

        $return = $mailer->sendViewTranslated(
            $user,
            'translatable.text',
            'test/template.tpl',
            ['dev' => true],
            'de_DE'
        );
        $this->assertEquals(1, $return);
    }

    /**
     * @covers \Engelsystem\Mail\EngelsystemMailer::getSubjectPrefix
     * @covers \Engelsystem\Mail\EngelsystemMailer::send
     * @covers \Engelsystem\Mail\EngelsystemMailer::setSubjectPrefix
     */
    public function testSend()
    {
        /** @var SwiftMessage|MockObject $message */
        $message = $this->createMock(SwiftMessage::class);
        /** @var SwiftMailer|MockObject $swiftMailer */
        $swiftMailer = $this->createMock(SwiftMailer::class);
        $this->setExpects($swiftMailer, 'createMessage', null, $message);
        $this->setExpects($swiftMailer, 'send', null, 1);
        $this->setExpects($message, 'setTo', [['to@xam.pel']], $message);
        $this->setExpects($message, 'setFrom', ['foo@bar.baz', 'Lorem Ipsum'], $message);
        $this->setExpects($message, 'setSubject', ['[Mail test] Foo Bar'], $message);
        $this->setExpects($message, 'setBody', ['Lorem Ipsum!'], $message);

        $mailer = new EngelsystemMailer($swiftMailer);
        $mailer->setFromAddress('foo@bar.baz');
        $mailer->setFromName('Lorem Ipsum');
        $mailer->setSubjectPrefix('Mail test');

        $this->assertEquals('Mail test', $mailer->getSubjectPrefix());

        $return = $mailer->send('to@xam.pel', 'Foo Bar', 'Lorem Ipsum!');
        $this->assertEquals(1, $return);
    }
}
