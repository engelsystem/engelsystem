<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Events\Listener;

use Engelsystem\Config\Config;
use Engelsystem\Events\Listener\News;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\News as NewsModel;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;

class NewsTest extends TestCase
{
    use HasDatabase;

    protected TestLogger $log;

    protected EngelsystemMailer | MockObject $mailer;

    protected NewsModel $news;

    protected User $user;

    /**
     * @covers \Engelsystem\Events\Listener\News::created
     * @covers \Engelsystem\Events\Listener\News::__construct
     * @covers \Engelsystem\Events\Listener\News::sendMail
     */
    public function testCreated(): void
    {
        $this->mailer->expects($this->once())
            ->method('sendViewTranslated')
            ->willReturnCallback(function (User $user, string $subject, string $template, array $data): bool {
                $this->assertEquals($this->user->id, $user->id);
                $this->assertEquals('notification.news.new', $subject);
                $this->assertEquals('emails/news-new', $template);
                $this->assertEquals('Foo', array_values($data)[0]);

                return true;
            });

        /** @var News $listener */
        $listener = $this->app->make(News::class);
        $listener->created($this->news);
    }

    /**
     * @covers \Engelsystem\Events\Listener\News::created
     * @covers \Engelsystem\Events\Listener\News::sendMail
     */
    public function testCreatedNoNotification(): void
    {
        $this->setExpects($this->mailer, 'sendViewTranslated', null, null, $this->never());

        /** @var News $listener */
        $listener = $this->app->make(News::class);
        $listener->created($this->news, false);
    }

    /**
     * @covers \Engelsystem\Events\Listener\News::updated
     * @covers \Engelsystem\Events\Listener\News::sendMail
     */
    public function testUpdated(): void
    {
        $this->mailer->expects($this->once())
            ->method('sendViewTranslated')
            ->willReturnCallback(function (User $user, string $subject, string $template, array $data): bool {
                $this->assertEquals($this->user->id, $user->id);
                $this->assertEquals('notification.news.updated', $subject);
                $this->assertEquals('emails/news-updated', $template);
                $this->assertEquals('Foo', array_values($data)[0]);

                return true;
            });

        /** @var News $listener */
        $listener = $this->app->make(News::class);
        $listener->updated($this->news);
    }

    /**
     * @covers \Engelsystem\Events\Listener\News::updated
     * @covers \Engelsystem\Events\Listener\News::sendMail
     */
    public function testUpdatedNoNotification(): void
    {
        $this->setExpects($this->mailer, 'sendViewTranslated', null, null, $this->never());

        /** @var News $listener */
        $listener = $this->app->make(News::class);
        $listener->updated($this->news, false);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();

        $this->log = new TestLogger();
        $this->app->instance(LoggerInterface::class, $this->log);

        $this->mailer = $this->createMock(EngelsystemMailer::class);
        $this->app->instance(EngelsystemMailer::class, $this->mailer);

        $this->app->instance('config', new Config());

        $this->news = NewsModel::factory(['title' => 'Foo'])->create();

        $this->user = User::factory()
            ->has(Settings::factory([
                'language' => '',
                'theme' => 1,
                'email_news' => true,
            ]))
            ->create();
    }
}
