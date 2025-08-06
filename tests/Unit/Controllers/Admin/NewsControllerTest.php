<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Engelsystem\Controllers\Admin\NewsController;
use Engelsystem\Controllers\NotificationType;
use Engelsystem\Events\EventDispatcher;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\News;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Controllers\ControllerTest;
use PHPUnit\Framework\MockObject\MockObject;

class NewsControllerTest extends ControllerTest
{
    protected Authenticator|MockObject $auth;
    protected EventDispatcher|MockObject $eventDispatcher;

    protected array $data = [
        [
            'title'      => 'Foo',
            'text'       => '**foo**',
            'user_id'    => 1,
        ],
    ];

    /**
     * @covers \Engelsystem\Controllers\Admin\NewsController::__construct
     * @covers \Engelsystem\Controllers\Admin\NewsController::edit
     * @covers \Engelsystem\Controllers\Admin\NewsController::showEdit
     */
    public function testEdit(): void
    {
        $this->request->attributes->set('news_id', 1);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/news/edit.twig', $view);

                $this->assertNotEmpty($data['news']);
                $this->assertFalse($data['send_notification']);

                return $this->response;
            });

        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);

        $controller->edit($this->request);
        $this->assertHasNoNotifications(NotificationType::WARNING);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\NewsController::edit
     */
    public function testEditIsMeeting(): void
    {
        $isMeeting = false;
        $this->response->expects($this->exactly(3))
            ->method('withView')
            ->willReturnCallback(
                function ($view, $data) use (&$isMeeting) {
                    $this->assertEquals($isMeeting, $data['is_meeting']);
                    $isMeeting = !$isMeeting;

                    return $this->response;
                }
            );

        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);

        // Is no meeting
        $controller->edit($this->request);

        // Is meeting
        $this->request->query->set('meeting', 1);
        $controller->edit($this->request);

        // Should stay no meeting
        $this->request->attributes->set('news_id', 1);
        $controller->edit($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\NewsController::save
     */
    public function testSaveCreateInvalid(): void
    {
        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);
        $controller->setValidator(new Validator());

        $this->expectException(ValidationException::class);
        $controller->save($this->request);
    }

    public function saveCreateEditProvider(): array
    {
        return [
            // Text, isMeeting, id, sendNotification
            ['Some test', true],
            ['Some test', false],
            ['Some test', false, 1],
            ['Some test', true, 1],
            ['Some test', false, null, true],
            ['Some test', false, 1, true],
        ];
    }

    /**
     * @covers       \Engelsystem\Controllers\Admin\NewsController::save
     * @dataProvider saveCreateEditProvider
     *
     */
    public function testSaveCreateEdit(
        string $text,
        bool $isMeeting,
        ?int $id = null,
        bool $sendNotification = false
    ): void {
        $this->request->attributes->set('news_id', $id);
        $body = [
            'title' => 'Some Title',
            'text' => $text,
        ];
        if ($isMeeting) {
            $body['is_meeting'] = '1';
        }
        if ($sendNotification) {
            $body['send_notification'] = '1';
        }

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function (string $event, array $payload) use ($id, $sendNotification) {
                $this->assertEquals($id ? 'news.updated' : 'news.created', $event);
                $this->assertEquals($sendNotification, $payload['sendNotification']);
                $this->assertInstanceOf(News::class, $payload['news']);

                return $this->eventDispatcher;
            });

        $this->addUser();
        $this->request = $this->request->withParsedBody($body);
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/news')
            ->willReturn($this->response);

        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);

        $this->assertTrue($this->log->hasInfoThatContains('Saved'));

        $this->assertHasNotification('news.edit.success');

        /** @var News $news */
        $news = News::find($id ?: 2);
        $this->assertEquals($text, $news->text);
        $this->assertEquals($isMeeting, (bool) $news->is_meeting);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\NewsController::save
     */
    public function testSaveNoContentChange(): void
    {
        /** @var News $news */
        $news = News::factory()->create([
            'is_pinned' => false,
        ]);
        $this->request->attributes->set('news_id', $news->id);
        $body = [
            'title' => $news->title,
            'text' => $news->text,
            'is_pinned' => '1',
        ];
        $this->addUser();
        $this->request = $this->request->withParsedBody($body);

        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);

        /** @var News $updatedNews */
        $updatedNews = News::find($news->id);
        $this->assertEquals($news->title, $updatedNews->title);
        $this->assertEquals($news->text, $updatedNews->text);
        $this->assertEquals($news->created_at, $updatedNews->created_at);
        $this->assertEquals($news->updated_at, $updatedNews->updated_at);
        $this->assertTrue($updatedNews->is_pinned);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\NewsController::save
     */
    public function testSavePreview(): void
    {
        $this->request->attributes->set('news_id', 1);
        $this->request = $this->request->withParsedBody([
            'title'          => 'New title',
            'text'           => 'New text',
            'is_meeting'     => '1',
            'is_pinned'      => '1',
            'is_highlighted' => '1',
            'preview'        => '1',
            'send_notification' => '1',
        ]);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/news/edit.twig', $view);

                /** @var News $news */
                $news = $data['news'];
                // Contains new text
                $this->assertTrue($news->is_meeting);
                $this->assertTrue($news->is_pinned);
                $this->assertTrue($news->is_highlighted);
                $this->assertEquals('New title', $news->title);
                $this->assertEquals('New text', $news->text);

                $this->assertTrue($data['send_notification']);

                return $this->response;
            });
        $this->auth->expects($this->atLeastOnce())
            ->method('can')
            ->with('news.highlight')
            ->willReturn(true);

        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);

        // Assert no changes
        $news = News::find(1);
        $this->assertEquals('Foo', $news->title);
        $this->assertEquals('**foo**', $news->text);
        $this->assertFalse($news->is_meeting);
        $this->assertFalse($news->is_pinned);
        $this->assertFalse($news->is_highlighted);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\NewsController::save
     * @covers \Engelsystem\Controllers\Admin\NewsController::delete
     */
    public function testSaveDelete(): void
    {
        $this->request->attributes->set('news_id', 1);
        $this->request = $this->request->withParsedBody([
            'title'  => '.',
            'text'   => '.',
            'delete' => '1',
        ]);
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/news')
            ->willReturn($this->response);

        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);

        $this->assertTrue($this->log->hasInfoThatContains('Deleted'));

        $this->assertHasNotification('news.delete.success');
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\NewsController::save
     */
    public function testSaveDuplicated(): void
    {
        $previousNews = News::first();
        $this->request = $this->request->withParsedBody([
            'title'  => $previousNews->title,
            'text'   => $previousNews->text,
        ]);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturn($this->response);

        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);

        $this->assertHasNotification('news.edit.duplicate', NotificationType::ERROR);
    }

    /**
     * Creates a new user
     */
    protected function addUser(): void
    {
        $user = User::factory(['id' => 42])
            ->has(Settings::factory(['email_news' => true]))
            ->create();

        $this->auth->expects($this->any())
            ->method('user')
            ->willReturn($user);
    }

    /**
     * Setup environment
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->auth = $this->createMock(Authenticator::class);
        $this->app->instance(Authenticator::class, $this->auth);

        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->eventDispatcher->expects(self::any())
            ->method('dispatch')
            ->willReturnSelf();
        $this->app->instance('events.dispatcher', $this->eventDispatcher);

        $user = User::factory()->create();
        (new News([
            'title'      => 'Foo',
            'text'       => '**foo**',
            'user_id'    => $user->id,
        ]))->save();
    }
}
