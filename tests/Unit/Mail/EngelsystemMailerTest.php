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
use PHPUnit\Framework\Attributes\CoversMethod;
use Psr\Log\NullLogger;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\RawMessage;

#[CoversMethod(EngelsystemMailer::class, '__construct')]
#[CoversMethod(EngelsystemMailer::class, 'sendView')]
#[CoversMethod(EngelsystemMailer::class, 'sendViewTranslated')]
#[CoversMethod(EngelsystemMailer::class, 'getSubjectPrefix')]
#[CoversMethod(EngelsystemMailer::class, 'send')]
#[CoversMethod(EngelsystemMailer::class, 'setSubjectPrefix')]
class EngelsystemMailerTest extends TestCase
{
    use HasDatabase;

    public function testSendView(): void
    {
        $view = $this->createMock(Renderer::class);
        $symfonyMailer = $this->getStubBuilder(MailerInterface::class)->getStub();
        $mailer = $this->getMockBuilder(EngelsystemMailer::class)
            ->setConstructorArgs(['log' => new NullLogger(), 'mailer' => $symfonyMailer, 'view' => $view])
            ->onlyMethods(['send'])
            ->getMock();
        $this->setExpects($mailer, 'send', ['foo@bar.baz', 'Lorem dolor', 'Rendered Stuff!'], true);
        $this->setExpects($view, 'render', ['test/template.tpl', ['dev' => true]], 'Rendered Stuff!');

        $status = $mailer->sendView('foo@bar.baz', 'Lorem dolor', 'test/template.tpl', ['dev' => true]);
        $this->assertTrue($status);
    }

    public function testSendViewTranslated(): void
    {
        $this->initDatabase();

        $user = User::factory(['email' => 'foo@bar.baz'])
            ->has(Settings::factory(['language' => 'de_DE']))
            ->has(Contact::factory(['email' => null]))
            ->create();

        $view = $this->createStub(Renderer::class);
        $symfonyMailer = $this->createStub(MailerInterface::class);
        $translator = $this->createMock(Translator::class);

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
        $matcher = $this->exactly(2);
        $translator->expects($matcher)
            ->method('setLocale')->willReturnCallback(function (...$parameters) use ($matcher): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('de_DE', $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('en_US', $parameters[0]);
                }
            });

        $status = $mailer->sendViewTranslated(
            $user,
            'translatable.text',
            'test/template.tpl',
            ['dev' => true],
            'de_DE'
        );
        $this->assertTrue($status);
    }

    public function testSend(): void
    {
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
