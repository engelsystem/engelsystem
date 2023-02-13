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
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Controllers\ControllerTest;
use PHPUnit\Framework\MockObject\MockObject;

class NewsControllerTest extends ControllerTest
{
    protected Authenticator|MockObject $auth;

    /** @var array */
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
            ['Some test', true],
            ['Some test', false],
            ['Some test', false, 1],
            ['Some test', true, 1],
        ];
    }

    /**
     * @covers       \Engelsystem\Controllers\Admin\NewsController::save
     * @dataProvider saveCreateEditProvider
     *
     * @param int|null $id
     */
    public function testSaveCreateEdit(
        string $text,
        bool $isMeeting,
        int $id = null
    ): void {
        $this->request->attributes->set('news_id', $id);
        $id = $id ?: 2;
        $body = [
            'title'      => 'Some Title',
            'text'       => $text,
        ];
        if ($isMeeting) {
            $body['is_meeting'] = '1';
        }

        $this->request = $this->request->withParsedBody($body);
        $this->addUser();
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/news')
            ->willReturn($this->response);

        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);

        $this->assertTrue($this->log->hasInfoThatContains('Updated'));

        $this->assertHasNotification('news.edit.success');

        $news = (new News())->find($id);
        $this->assertEquals($text, $news->text);
        $this->assertEquals($isMeeting, (bool) $news->is_meeting);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\NewsController::save
     */
    public function testSavePreview(): void
    {
        $this->request->attributes->set('news_id', 1);
        $this->request = $this->request->withParsedBody([
            'title'        => 'New title',
            'text'         => 'New text',
            'is_meeting'   => '1',
            'is_pinned'    => '1',
            'is_important' => '1',
            'preview'      => '1',
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
                $this->assertTrue($news->is_important);
                $this->assertEquals('New title', $news->title);
                $this->assertEquals('New text', $news->text);

                return $this->response;
            });
        $this->auth->expects($this->atLeastOnce())
            ->method('can')
            ->with('news.important')
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
        $this->assertFalse($news->is_important);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\NewsController::save
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
     * Creates a new user
     */
    protected function addUser(): void
    {
        $user = User::factory(['id' => 42])->create();

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

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher->expects(self::any())
            ->method('dispatch')
            ->willReturnSelf();
        $this->app->instance('events.dispatcher', $eventDispatcher);

        (new News([
            'title'      => 'Foo',
            'text'       => '**foo**',
            'user_id'    => 1,
        ]))->save();
    }
}
