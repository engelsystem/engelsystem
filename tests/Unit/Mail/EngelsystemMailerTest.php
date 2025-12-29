<?php

declare(strict_types=1);

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
use Psr\Log\NullLogger;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;

class EngelsystemMailerTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Mail\EngelsystemMailer::__construct
     * @covers \Engelsystem\Mail\EngelsystemMailer::sendView
     */
    public function testSendView(): void
    {
        /** @var Renderer|MockObject $view */
        $view = $this->createMock(Renderer::class);
        /** @var MailerInterface|MockObject $symfonyMailer */
        $symfonyMailer = $this->getMockForAbstractClass(MailerInterface::class);
        /** @var EngelsystemMailer|MockObject $mailer */
        $mailer = $this->getMockBuilder(EngelsystemMailer::class)
            ->setConstructorArgs(['log' => new NullLogger(), 'mailer' => $symfonyMailer, 'view' => $view])
            ->onlyMethods(['send'])
            ->getMock();
        $this->setExpects($mailer, 'send', ['foo@bar.baz', 'Lorem dolor', 'Rendered Stuff!'], true);
        $this->setExpects($view, 'render', ['test/template.tpl', ['dev' => true]], 'Rendered Stuff!');

        $status = $mailer->sendView('foo@bar.baz', 'Lorem dolor', 'test/template.tpl', ['dev' => true]);
        $this->assertTrue($status);
    }

    /**
     * @covers \Engelsystem\Mail\EngelsystemMailer::sendViewTranslated
     */
    public function testSendViewTranslated(): void
    {
        $this->initDatabase();

        $user = User::factory(['email' => 'foo@bar.baz'])
            ->has(Settings::factory(['language' => 'de_DE']))
            ->has(Contact::factory(['email' => null]))
            ->create();

        /** @var Renderer|MockObject $view */
        $view = $this->createMock(Renderer::class);
        /** @var MailerInterface|MockObject $symfonyMailer */
        $symfonyMailer = $this->createMock(MailerInterface::class);
        /** @var Translator|MockObject $translator */
        $translator = $this->createMock(Translator::class);

        /** @var EngelsystemMailer|MockObject $mailer */
        $mailer = $this->getMockBuilder(EngelsystemMailer::class)
            ->setConstructorArgs([
                'log' => new NullLogger(),
                'mailer' => $symfonyMailer,
                'view' => $view,
                'translation' => $translator,
            ])
            ->onlyMethods(['sendView'])
            ->getMock();

        $this->setExpects(
            $mailer,
            'sendView',
            ['foo@bar.baz', 'Lorem dolor', 'test/template.tpl', ['dev' => true]],
            true
        );
        $this->setExpects($translator, 'getLocales', null, ['de_DE', 'en_US']);
        $this->setExpects($translator, 'getLocale', null, 'en_US');
        $this->setExpects($translator, 'translate', ['translatable.text', ['dev' => true]], 'Lorem dolor');
        $translator->expects($this->exactly(2))
            ->method('setLocale')
            ->withConsecutive(['de_DE'], ['en_US']);

        $status = $mailer->sendViewTranslated(
            $user,
            'translatable.text',
            'test/template.tpl',
            ['dev' => true],
            'de_DE'
        );
        $this->assertTrue($status);
    }

    /**
     * @covers \Engelsystem\Mail\EngelsystemMailer::getSubjectPrefix
     * @covers \Engelsystem\Mail\EngelsystemMailer::send
     * @covers \Engelsystem\Mail\EngelsystemMailer::setSubjectPrefix
     */
    public function testSend(): void
    {
        /** @var MailerInterface|MockObject $symfonyMailer */
        $symfonyMailer = $this->createMock(MailerInterface::class);

        $symfonyMailer->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (RawMessage $message, ?Envelope $envelope = null): void {
                $this->assertStringContainsString('foo@bar.baz', $message->toString());
                $this->assertStringContainsString('Foo Bar', $message->toString());
                $this->assertStringContainsString('Mail test', $message->toString());
                $this->assertStringContainsString('to@xam.pel', $message->toString());
                $this->assertStringContainsString('Lorem Ipsum!', $message->toString());
            });

        $mailer = new EngelsystemMailer(new NullLogger(), $symfonyMailer);
        $mailer->setFromAddress('foo@bar.baz');
        $mailer->setFromName('Foo Bar');
        $mailer->setSubjectPrefix('Mail test');

        $this->assertEquals('Mail test', $mailer->getSubjectPrefix());

        $status = $mailer->send('to@xam.pel', 'Foo Bar ', 'Lorem Ipsum!');
        $this->assertTrue($status);
    }
}
