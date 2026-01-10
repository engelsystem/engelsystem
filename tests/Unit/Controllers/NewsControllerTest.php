<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\NewsController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\News;
use Engelsystem\Models\NewsComment;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Stub;

#[CoversMethod(NewsController::class, '__construct')]
#[CoversMethod(NewsController::class, 'index')]
#[CoversMethod(NewsController::class, 'meetings')]
#[CoversMethod(NewsController::class, 'showOverview')]
#[CoversMethod(NewsController::class, 'renderView')]
#[CoversMethod(NewsController::class, 'show')]
#[CoversMethod(NewsController::class, 'comment')]
#[CoversMethod(NewsController::class, 'deleteComment')]
#[AllowMockObjectsWithoutExpectations]
class NewsControllerTest extends ControllerTestCase
{
    use HasDatabase;

    protected Authenticator&Stub $auth;

    protected array $data = [
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
            'title'          => 'baz',
            'text'           => 'baz',
            'is_meeting'     => true,
            'is_pinned'      => true,
            'is_highlighted' => true,
            'user_id'        => 1,
        ],
        [
            'title'      => 'Lorem',
            'text'       => 'lorem',
            'is_meeting' => false,
            'is_pinned'  => false,
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
            'is_pinned'  => false,
            'user_id'    => 1,
        ],
    ];

    public function testIndex(): void
    {
        $this->request->attributes->set('page', 2);

        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);

        $n = 1;
        $this->response->expects($this->exactly(3))
            ->method('withView')
            ->willReturnCallback(
                function (string $page, array $data) use (&$n) {
                    $this->assertEquals('pages/news/index.twig', $page);
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
                            $this->assertEquals('Bar', $news[0]->title);
                            break;
                        case 2:
                            // Show meetings
                            $this->assertTrue($data['only_meetings']);
                            $this->assertTrue($news->isNotEmpty());
                            $this->assertEquals(2, $data['pages']);
                            $this->assertEquals(2, $data['page']);
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

    public function testShow(): void
    {
        $this->request->attributes->set('news_id', 1);
        $this->response->expects($this->once())
            ->method('withView')
            ->with('pages/news/news.twig')
            ->willReturn($this->response);

        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);

        $controller->show($this->request);
    }

    public function testShowNotFound(): void
    {
        $this->request->attributes->set('news_id', 42);

        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);

        $this->expectException(ModelNotFoundException::class);
        $controller->show($this->request);
    }

    public function testCommentInvalid(): void
    {
        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);
        $controller->setValidator(new Validator());

        $this->expectException(ValidationException::class);
        $controller->comment($this->request);
    }

    public function testCommentNewsNotFound(): void
    {
        $this->request->attributes->set('news_id', 42);
        $this->request = $this->request->withParsedBody(['comment' => 'Foo bar!']);
        $this->addUser();

        /** @var NewsController $controller */
        $controller = $this->app->make(NewsController::class);
        $controller->setValidator(new Validator());

        $this->expectException(ModelNotFoundException::class);
        $controller->comment($this->request);
    }

    public function testComment(): void
    {
        $this->request->attributes->set('news_id', 1);
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
        $comment = NewsComment::whereNewsId(1)->get()[2];
        $this->assertEquals('Foo bar!', $comment->text);
    }

    public function testDeleteCommentInvalidRequest(): void
    {
        /** @var NewsController $controller */
        $controller = $this->app->get(NewsController::class);
        $controller->setValidator($this->app->get(Validator::class));

        $this->expectException(ValidationException::class);
        $controller->deleteComment($this->request);
    }

    public function testDeleteCommentNotFound(): void
    {
        $this->request = $this->request->withAttribute('news_id', 42)->withParsedBody(['delete' => '1']);

        /** @var NewsController $controller */
        $controller = $this->app->get(NewsController::class);
        $controller->setValidator($this->app->get(Validator::class));

        $this->expectException(ModelNotFoundException::class);
        $controller->deleteComment($this->request);
    }

    public function testDeleteCommentNotAllowed(): void
    {
        $this->request = $this->request->withAttribute('comment_id', 2)->withParsedBody(['delete' => '1']);

        /** @var NewsController $controller */
        $controller = $this->app->get(NewsController::class);
        $controller->setValidator($this->app->get(Validator::class));

        $this->expectException(HttpForbidden::class);
        $controller->deleteComment($this->request);
    }

    public function testDeleteComment(): void
    {
        $this->request = $this->request->withAttribute('comment_id', 1)->withParsedBody(['delete' => '1']);
        $this->setExpects($this->response, 'redirectTo', ['http://localhost/news/1'], $this->response);

        /** @var NewsController $controller */
        $controller = $this->app->get(NewsController::class);
        $controller->setValidator($this->app->get(Validator::class));

        $controller->deleteComment($this->request);

        $this->assertCount(1, NewsComment::all());
        $this->assertTrue($this->log->hasInfoThatContains('Deleted comment'));
        $this->assertHasNotification('news.comment-delete.success');
    }

    /**
     * Setup environment
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->config->set(['display_news' => 2]);

        $this->auth = $this->createStub(Authenticator::class);
        $this->app->instance(Authenticator::class, $this->auth);

        $this->addUser(1);
        $this->addUser(2);

        foreach ($this->data as $news) {
            (new News($news))->save();
        }

        foreach ([1, 2] as $i) {
            NewsComment::create([
                'news_id' => 1,
                'text'    => 'test comment ' . $i,
                'user_id' => $i,
            ]);
        }
    }

    /**
     * Creates a new user
     */
    protected function addUser(int $id = 42): void
    {
        $user = User::factory()->create(['id' => $id]);

        $this->auth->method('user')
            ->willReturn($user);
    }
}
