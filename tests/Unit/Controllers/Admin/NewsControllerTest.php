<?php

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Engelsystem\Controllers\Admin\NewsController;
use Engelsystem\Events\EventDispatcher;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\News;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Controllers\ControllerTest;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;

class NewsControllerTest extends ControllerTest
{
    /** @var Authenticator|MockObject */
    protected $auth;

    /** @var array */
    protected $data = [
        [
            'title'      => 'Foo',
            'text'       => '**foo**',
            'user_id'    => 1,
        ]
    ];

    /**
     * @covers \Engelsystem\Controllers\Admin\NewsController::__construct
     * @covers \Engelsystem\Controllers\Admin\NewsController::edit
     * @covers \Engelsystem\Controllers\Admin\NewsController::showEdit
     */
    public function testEdit()
    {
        $this->request->attributes->set('id', 1);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/news/edit.twig', $view);

                /** @var Collection $warnings */
                $warnings = $data['warnings'];
                $this->assertNotEmpty($data['news']);
                $this->assertTrue($warnings->isEmpty());

                return $this->response;
            });

        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);

        $controller->edit($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\NewsController::edit
     */
    public function testEditIsMeeting()
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
        $this->request->attributes->set('id', 1);
        $controller->edit($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\NewsController::save
     */
    public function testSaveCreateInvalid()
    {
        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);
        $controller->setValidator(new Validator());

        $this->expectException(ValidationException::class);
        $controller->save($this->request);
    }

    /**
     * @return array
     */
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
     * @param string $text
     * @param bool $isMeeting
     * @param int|null $id
     */
    public function testSaveCreateEdit(
        string $text,
        bool $isMeeting,
        int $id = null
    ) {
        $this->request->attributes->set('id', $id);
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

        /** @var Session $session */
        $session = $this->app->get('session');
        $messages = $session->get('messages');
        $this->assertEquals('news.edit.success', $messages[0]);

        $news = (new News())->find($id);
        $this->assertEquals($text, $news->text);
        $this->assertEquals($isMeeting, (bool)$news->is_meeting);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\NewsController::save
     */
    public function testSavePreview()
    {
        $this->request->attributes->set('id', 1);
        $this->request = $this->request->withParsedBody([
            'title'      => 'New title',
            'text'       => 'New text',
            'is_meeting' => '1',
            'is_pinned'  => '1',
            'preview'    => '1',
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
                $this->assertEquals('New title', $news->title);
                $this->assertEquals('New text', $news->text);

                return $this->response;
            });

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
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\NewsController::save
     */
    public function testSaveDelete()
    {
        $this->request->attributes->set('id', 1);
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

        /** @var Session $session */
        $session = $this->app->get('session');
        $messages = $session->get('messages');
        $this->assertEquals('news.delete.success', $messages[0]);
    }

    /**
     * Creates a new user
     */
    protected function addUser()
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
        $this->app->instance('events.dispatcher', $eventDispatcher);

        (new News([
            'title'      => 'Foo',
            'text'       => '**foo**',
            'user_id'    => 1,
        ]))->save();
    }
}
