<?php
declare(strict_types=1);

use Engelsystem\Models\News\News;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;

/**
 * This class provides tests for the News model.
 */
class NewsTest extends TestCase
{
    use HasDatabase;

    /**
     * @var array
     */
    private $newsData;

    /**
     * @var User
     */
    private $user;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();

        $this->user = User::make([
            'name'     => 'lorem',
            'password' => '',
            'email'    => 'foo@bar.batz',
            'api_key'  => '',
        ]);
        $this->user->save();

        $this->newsData = [
            'title'   => 'test title',
            'text'    => 'test text',
            'user_id' => $this->user->id
        ];
    }

    /**
     * Tests that creating a News item with default values works.
     *
     * @return void
     */
    public function testCreateDefault(): void
    {
        $news = News::create($this->newsData);

        $this->assertSame(1, $news->id);
        $this->assertSame($this->newsData['title'], $news->title);
        $this->assertSame($this->newsData['text'], $news->text);
        $this->assertFalse($news->is_meeting);
    }

    /**
     * Tests that creating a News item with all fill values works.
     *
     * @return void
     */
    public function testCreate(): void
    {
        $news = News::create(
            $this->newsData + ['is_meeting' => true,]
        );

        $this->assertSame(1, $news->id);
        $this->assertSame($this->newsData['title'], $news->title);
        $this->assertSame($this->newsData['text'], $news->text);
        $this->assertTrue($news->is_meeting);
    }
}
