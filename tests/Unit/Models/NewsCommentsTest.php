<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\News;
use Engelsystem\Models\NewsComment;
use Engelsystem\Models\User\User;

/**
 * This class provides tests for the NewsComments model.
 */
class NewsCommentsTest extends ModelTest
{
    /** @var User */
    private $user;

    /** @var News */
    private $news;

    /** @var array */
    private $newsCommentData;

    /**
     * Sets up some test objects and test data.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

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
     * @covers \Engelsystem\Models\NewsComment
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
     * @covers \Engelsystem\Models\NewsComment::user
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
     * @covers \Engelsystem\Models\NewsComment::news
     */
    public function testNews(): void
    {
        $newsComment = NewsComment::create($this->newsCommentData);
        $this->assertInstanceOf(News::class, $newsComment->news);
        $this->assertSame($this->news->id, $newsComment->news->id);
    }
}
