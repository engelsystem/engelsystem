<?php

namespace Engelsystem\Test\Unit\Events\Listener;

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
use Swift_SwiftException as SwiftException;

class NewsTest extends TestCase
{
    use HasDatabase;

    /** @var TestLogger */
    protected $log;

    /** @var EngelsystemMailer|MockObject */
    protected $mailer;

    /** @var User */
    protected $user;

    /**
     * @covers \Engelsystem\Events\Listener\News::created
     * @covers \Engelsystem\Events\Listener\News::__construct
     * @covers \Engelsystem\Events\Listener\News::sendMail
     */
    public function testCreated()
    {
        /** @var NewsModel $news */
        $news = NewsModel::factory(['title' => 'Foo'])->create();

        $i = 0;
        $this->mailer->expects($this->exactly(2))
            ->method('sendViewTranslated')
            ->willReturnCallback(function (User $user, string $subject, string $template, array $data) use (&$i) {
                $this->assertEquals(1, $user->id);
                $this->assertEquals('notification.news.new', $subject);
                $this->assertEquals('emails/news-new', $template);
                $this->assertEquals('Foo', array_values($data)[0]);

                if ($i++ > 0) {
                    throw new SwiftException('Oops');
                }

                return 1;
            });

        /** @var News $listener */
        $listener = $this->app->make(News::class);
        $error = 'Unable to send email';

        $listener->created($news);
        $this->assertFalse($this->log->hasErrorThatContains($error));

        $listener->created($news);
        $this->assertTrue($this->log->hasErrorThatContains($error));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();

        $this->log = new TestLogger();
        $this->app->instance(LoggerInterface::class, $this->log);

        $this->mailer = $this->createMock(EngelsystemMailer::class);
        $this->app->instance(EngelsystemMailer::class, $this->mailer);

        $this->user = User::factory()
            ->has(Settings::factory([
                'language' => '',
                'theme' => 1,
                'email_news' => true,
            ]))
            ->create();
    }
}
