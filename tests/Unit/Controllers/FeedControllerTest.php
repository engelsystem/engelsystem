<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\FeedController;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Models\News;
use Illuminate\Support\Collection;

class FeedControllerTest extends ControllerTest
{
    /**
     * @covers \Engelsystem\Controllers\FeedController::__construct
     * @covers \Engelsystem\Controllers\FeedController::atom
     */
    public function testAtom(): void
    {
        $controller = new FeedController($this->request, $this->response);

        $this->setExpects(
            $this->response,
            'withHeader',
            ['content-type', 'application/atom+xml; charset=utf-8'],
            $this->response
        );
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('api/atom', $view);
                $this->assertArrayHasKey('news', $data);

                return $this->response;
            });

        $controller->atom();
    }

    /**
     * @covers \Engelsystem\Controllers\FeedController::rss
     */
    public function testRss(): void
    {
        $controller = new FeedController($this->request, $this->response);

        $this->setExpects(
            $this->response,
            'withHeader',
            ['content-type', 'application/rss+xml; charset=utf-8'],
            $this->response
        );
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('api/rss', $view);
                $this->assertArrayHasKey('news', $data);

                return $this->response;
            });
        $controller->rss();
    }

    public function getNewsMeetingsDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @covers       \Engelsystem\Controllers\FeedController::getNews
     * @dataProvider getNewsMeetingsDataProvider
     */
    public function testGetNewsMeetings(bool $isMeeting): void
    {
        $controller = new FeedController($this->request, $this->response);

        $this->request->attributes->set('meetings', $isMeeting);
        $this->setExpects($this->response, 'withHeader', null, $this->response);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) use ($isMeeting) {
                /** @var Collection|News[] $newsList */
                $newsList = $data['news'];
                $this->assertCount($isMeeting ? 5 : 7, $newsList);

                foreach ($newsList as $news) {
                    $this->assertEquals($isMeeting, $news->is_meeting);
                }

                return $this->response;
            });

        $controller->rss();
    }

    /**
     * @covers \Engelsystem\Controllers\FeedController::getNews
     */
    public function testGetNewsLimit(): void
    {
        News::query()->where('id', '<>', 1)->update(['updated_at' => Carbon::now()->subHour()]);
        $controller = new FeedController($this->request, $this->response);

        $this->setExpects($this->response, 'withHeader', null, $this->response);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertCount(10, $data['news']);

                /** @var News $news1 */
                $news1 = $data['news'][0];
                /** @var News $news2 */
                $news2 = $data['news'][1];
                $this->assertTrue($news1->updated_at > $news2->updated_at, 'First news must be up to date');

                return $this->response;
            });

        $controller->rss();
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->config->set('display_news', 10);

        News::factory(7)->create(['is_meeting' => false]);
        News::factory(5)->create(['is_meeting' => true]);
    }
}
