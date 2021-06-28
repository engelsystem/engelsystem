<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\NewsController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\News;
use Engelsystem\Models\NewsComment;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class NewsControllerTest extends TestCase
{
    use HasDatabase;

    /** @var Authenticator|MockObject */
    protected $auth;

    /** @var array */
    protected $data = [
        [
            'title'      => 'Foo',
            'text'       => 'foo',
            'is_meeting' => false,
            'user_id'    => 1,
        ],
        [
            'title'      => 'Bar',
            'text'       => 'bar',
            'is_meeting' => false,
            'is_pinned'  => true,
            'user_id'    => 1,
        ],
        [
            'title'      => 'baz',
            'text'       => 'baz',
            'is_meeting' => true,
            'is_pinned'  => true,
            'user_id'    => 1,
        ],
        [
            'title'      => 'Lorem',
            'text'       => 'lorem',
            'is_meeting' => false,
            'user_id'    => 1,
        ],
        [
            'title'      => 'Ipsum',
            'text'       => 'ipsum',
            'is_meeting' => true,
            'is_pinned'  => true,
            'user_id'    => 1,
        ],
        [
            'title'      => 'Dolor',
            'text'       => 'test',
            'is_meeting' => true,
            'user_id'    => 1,
        ],
    ];

    /** @var TestLogger */
    protected $log;

    /** @var Response|MockObject */
    protected $response;

    /** @var Request */
    protected $request;

    /**
     * @covers \Engelsystem\Controllers\NewsController::__construct
     * @covers \Engelsystem\Controllers\NewsController::index
     * @covers \Engelsystem\Controllers\NewsController::meetings
     * @covers \Engelsystem\Controllers\NewsController::showOverview
     * @covers \Engelsystem\Controllers\NewsController::renderView
     */
    public function testIndex()
    {
        $this->request->attributes->set('page', 2);

        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);

        $n = 1;
        $this->response->expects($this->exactly(3))
            ->method('withView')
            ->willReturnCallback(
                function (string $page, array $data) use (&$n) {
                    $this->assertEquals('pages/news/overview.twig', $page);
                    /** @var Collection $news */
                    $news = $data['news'];

                    switch ($n) {
                        case 1:
                            // Show everything
                            $this->assertFalse($data['only_meetings']);
                            $this->assertTrue($news->isNotEmpty());
                            $this->assertEquals(3, $data['pages']);
                            $this->assertEquals(2, $data['page']);
                            $this->assertTrue($news[0]->is_pinned);
                            $this->assertEquals('Ipsum', $news[0]->title);
                            break;
                        case 2:
                            // Show meetings
                            $this->assertTrue($data['only_meetings']);
                            $this->assertTrue($news->isNotEmpty());
                            $this->assertEquals(1, $data['pages']);
                            $this->assertEquals(1, $data['page']);
                            break;
                        default:
                            // No news found
                            $this->assertTrue($news->isEmpty());
                            $this->assertEquals(1, $data['pages']);
                            $this->assertEquals(1, $data['page']);
                    }

                    $n++;
                    return $this->response;
                }
            );

        $controller->index();
        $controller->meetings();

        News::query()->truncate();
        $controller->index();
    }

    /**
     * @covers \Engelsystem\Controllers\NewsController::show
     */
    public function testShow()
    {
        $this->request->attributes->set('id', 1);
        $this->response->expects($this->once())
            ->method('withView')
            ->with('pages/news/news.twig')
            ->willReturn($this->response);

        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);

        $controller->show($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\NewsController::show
     */
    public function testShowNotFound()
    {
        $this->request->attributes->set('id', 42);

        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);

        $this->expectException(ModelNotFoundException::class);
        $controller->show($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\NewsController::comment
     */
    public function testCommentInvalid()
    {
        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);
        $controller->setValidator(new Validator());

        $this->expectException(ValidationException::class);
        $controller->comment($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\NewsController::comment
     */
    public function testCommentNewsNotFound()
    {
        $this->request->attributes->set('id', 42);
        $this->request = $this->request->withParsedBody(['comment' => 'Foo bar!']);
        $this->addUser();

        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);
        $controller->setValidator(new Validator());

        $this->expectException(ModelNotFoundException::class);
        $controller->comment($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\NewsController::comment
     */
    public function testComment()
    {
        $this->request->attributes->set('id', 1);
        $this->request = $this->request->withParsedBody(['comment' => 'Foo bar!']);
        $this->addUser();

        $this->response->expects($this->once())
            ->method('redirectTo')
            ->willReturn($this->response);

        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);
        $controller->setValidator(new Validator());

        $controller->comment($this->request);
        $this->log->hasInfoThatContains('Created news comment');

        /** @var NewsComment $comment */
        $comment = NewsComment::whereNewsId(1)->first();
        $this->assertEquals('Foo bar!', $comment->text);
    }

    /**
     * Setup environment
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();

        $this->request = new Request();
        $this->app->instance('request', $this->request);
        $this->app->instance(Request::class, $this->request);
        $this->app->instance(ServerRequestInterface::class, $this->request);

        $this->response = $this->createMock(Response::class);
        $this->app->instance(Response::class, $this->response);

        $this->app->instance(Config::class, new Config(['display_news' => 2]));

        $this->log = new TestLogger();
        $this->app->instance(LoggerInterface::class, $this->log);

        $this->app->instance('session', new Session(new MockArraySessionStorage()));

        $this->auth = $this->createMock(Authenticator::class);
        $this->app->instance(Authenticator::class, $this->auth);

        $this->app->bind(UrlGeneratorInterface::class, UrlGenerator::class);

        $this->app->instance('config', new Config());

        foreach ($this->data as $news) {
            (new News($news))->save();
        }
    }

    /**
     * Creates a new user
     */
    protected function addUser()
    {
        $user = User::factory()->create(['id' => 42]);

        $this->auth->expects($this->any())
            ->method('user')
            ->willReturn($user);
    }
}
