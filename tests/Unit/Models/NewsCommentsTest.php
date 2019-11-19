<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\News;
use Engelsystem\Models\NewsComment;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;

/**
 * This class provides tests for the NewsComments model.
 */
class NewsCommentsTest extends TestCase
{
    use HasDatabase;

    /** @var User */
    private $user;

    /** @var News */
    private $news;

    /** @var array */
    private $newsCommentData;

    /**
     * Sets up some test objects and test data.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();

        $this->user = User::create([
            'name'     => 'lorem',
            'password' => '',
            'email'    => 'lorem@example.com',
            'api_key'  => '',
        ]);

        $this->news = News::create([
            'title'   => 'test title',
            'text'    => 'test text',
            'user_id' => $this->user->id,
        ]);

        $this->newsCommentData = [
            'news_id' => $this->news->id,
            'text'    => 'test comment',
            'user_id' => $this->user->id,
        ];
    }

    /**
     * Tests that a NewsComment can be created and loaded.
     *
     * @return void
     */
    public function testCreate(): void
    {
        $createdNewsComment = NewsComment::create($this->newsCommentData);

        $newsComment = NewsComment::find($createdNewsComment->id);
        $this->assertInstanceOf(NewsComment::class, $newsComment);
        $this->assertEquals($this->newsCommentData['news_id'], $newsComment->news_id);
        $this->assertSame($this->newsCommentData['text'], $newsComment->text);
        $this->assertEquals($this->newsCommentData['user_id'], $newsComment->user_id);
    }

    /**
     * Tests that accessing the User of a NewsComment works.
     *
     * @return void
     */
    public function testUser(): void
    {
        $newsComment = NewsComment::create($this->newsCommentData);
        $this->assertInstanceOf(User::class, $newsComment->user);
        $this->assertSame($this->user->id, $newsComment->user->id);
    }

    /**
     * Tests that accessing the News of a NewsComment works.
     *
     * @return void
     */
    public function testNews(): void
    {
        $newsComment = NewsComment::create($this->newsCommentData);
        $this->assertInstanceOf(News::class, $newsComment->news);
        $this->assertSame($this->news->id, $newsComment->news->id);
    }

    /**
     * Tests that accessing the NewsComments of a News works.
     *
     * @return void
     */
    public function testNewsComments(): void
    {
        $newsComment = NewsComment::create($this->newsCommentData);
        $comments = $this->news->comments;
        $this->assertCount(1, $comments);
        $comment = $comments->first();
        $this->assertSame($newsComment->id, $comment->id);
    }

    /**
     * Tests that accessing the NewsComments of an User works.
     *
     * @return void
     */
    public function testUserNewsComments(): void
    {
        $newsComment = NewsComment::create($this->newsCommentData);
        $comments = $this->user->newsComments;
        $this->assertCount(1, $comments);
        $comment = $comments->first();
        $this->assertSame($newsComment->id, $comment->id);
    }
}
