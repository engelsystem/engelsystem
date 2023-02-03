<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\FeedController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Models\News;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class FeedControllerTest extends ControllerTest
{
    protected Authenticator|MockObject $auth;

    /**
     * @covers \Engelsystem\Controllers\FeedController::__construct
     * @covers \Engelsystem\Controllers\FeedController::atom
     */
    public function testAtom(): void
    {
        $controller = new FeedController($this->auth, $this->request, $this->response);

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
        $controller = new FeedController($this->auth, $this->request, $this->response);

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

    /**
     * @covers \Engelsystem\Controllers\FeedController::ical
     * @covers \Engelsystem\Controllers\FeedController::getShifts
     */
    public function testIcal(): void
    {
        $this->request = $this->request->withQueryParams(['key' => 'fo0']);
        $this->auth = new Authenticator(
            $this->request,
            new Session(new MockArraySessionStorage()),
            new User(),
        );
        $controller = new FeedController($this->auth, $this->request, $this->response);

        /** @var User $user */
        $user = User::factory()->create(['api_key' => 'fo0']);
        ShiftEntry::factory(3)->create(['user_id' => $user->id]);

        $this->response->expects($this->exactly(2))
            ->method('withHeader')
            ->withConsecutive(
                ['content-type', 'text/calendar; charset=utf-8'],
                ['content-disposition', 'attachment; filename=shifts.ics']
            )
            ->willReturn($this->response);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('api/ical', $view);
                $this->assertArrayHasKey('shiftEntries', $data);

                /** @var ShiftEntry[]|Collection $shiftEntries */
                $shiftEntries = $data['shiftEntries'];
                $this->assertCount(3, $shiftEntries);

                $this->assertTrue($shiftEntries[0]->shift->start < $shiftEntries[1]->shift->start);

                return $this->response;
            });
        $controller->ical();
    }

    /**
     * @covers \Engelsystem\Controllers\FeedController::shifts
     * @covers \Engelsystem\Controllers\FeedController::getShifts
     */
    public function testShifts(): void
    {
        $this->request = $this->request->withQueryParams(['key' => 'fo0']);
        $this->auth = new Authenticator(
            $this->request,
            new Session(new MockArraySessionStorage()),
            new User(),
        );
        $controller = new FeedController($this->auth, $this->request, $this->response);

        /** @var User $user */
        $user = User::factory()->create(['api_key' => 'fo0']);
        ShiftEntry::factory(3)->create(['user_id' => $user->id]);

        $this->setExpects(
            $this->response,
            'withAddedHeader',
            ['content-type', 'application/json; charset=utf-8'],
            $this->response
        );

        $this->response->expects($this->once())
            ->method('withContent')
            ->willReturnCallback(function ($jsonData) {
                $data = json_decode($jsonData, true);
                $this->assertIsArray($data);

                $this->assertCount(3, $data);
                $this->assertTrue($data[0]['start'] < $data[1]['start']);

                // Ensure dates exist used by Fahrplan app
                foreach (
                    [
                        'name', 'title', 'description',
                        'Comment',
                        'SID', 'shifttype_id', 'URL',
                        'RID', 'Name', 'map_url',
                        'start', 'start_date', 'end', 'end_date',
                        'timezone', 'event_timezone',
                    ] as $requiredAttribute
                ) {
                    $this->assertArrayHasKey($requiredAttribute, $data[0]);
                }

                return $this->response;
            });
        $controller->shifts();
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
        $controller = new FeedController($this->auth, $this->request, $this->response);

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
        $controller = new FeedController($this->auth, $this->request, $this->response);

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
        $this->auth = $this->createMock(Authenticator::class);

        News::factory(7)->create(['is_meeting' => false]);
        News::factory(5)->create(['is_meeting' => true]);
    }
}
