<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\News;
use Engelsystem\Models\User\User;

/**
 * This class provides tests for the News model.
 */
class NewsTest extends ModelTest
{
    /** @var array */
    private array $newsData;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->newsData = [
            'title'   => 'test title',
            'text'    => 'test text',
            'user_id' => $this->user->id
        ];
    }

    /**
     * Tests that creating a News item with default values works.
     *
     * @covers \Engelsystem\Models\News
     */
    public function testCreateDefault(): void
    {
        $news = (new News())->create($this->newsData);
        $news = $news->find($news->id);

        $this->assertSame(1, $news->id);
        $this->assertSame($this->newsData['title'], $news->title);
        $this->assertSame($this->newsData['text'], $news->text);
        $this->assertFalse($news->is_meeting);
    }

    /**
     * Tests that accessing the NewsComments of a News works.
     *
     * @covers \Engelsystem\Models\News::comments
     */
    public function testNewsComments(): void
    {
        $news = (new News())->create($this->newsData);
        $comment = $news->comments()->create(['text' => 'test comment', 'user_id' => $this->user->id]);

        $comments = $news->comments;
        $this->assertCount(1, $comments);
        $this->assertEquals($comment->toArray(), $news->comments->first()->toArray());
    }

    /**
     * Tests that text more tags work
     *
     * @covers \Engelsystem\Models\News::text
     */
    public function testTextMore(): void
    {
        $news = new News($this->newsData);

        $news->text = "Foo\n\n\nBar";
        $this->assertEquals("Foo\n\n\nBar", $news->text);
        $this->assertEquals("Foo\n\n\nBar", $news->text());
        $this->assertEquals("Foo\n\n\nBar", $news->text(false));

        $news->text = "Foo\n[more]\nBar";
        $this->assertEquals("Foo\n[more]\nBar", $news->text);
        $this->assertEquals("Foo\n\nBar", $news->text());
        $this->assertEquals('Foo', $news->text(false));
    }

    /**
     * Tests that creating a News item with all fill values works.
     *
     * @covers \Engelsystem\Models\News
     */
    public function testCreate(): void
    {
        $news = (new News())->create(
            $this->newsData + ['is_meeting' => true, 'is_pinned' => true]
        );
        $news = $news->find($news->id);

        $this->assertSame(1, $news->id);
        $this->assertSame($this->newsData['title'], $news->title);
        $this->assertSame($this->newsData['text'], $news->text);
        $this->assertTrue($news->is_meeting);
        $this->assertTrue($news->is_pinned);
    }
}
