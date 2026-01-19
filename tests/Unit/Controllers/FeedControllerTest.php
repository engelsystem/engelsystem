<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\FeedController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Models\News;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

#[CoversMethod(FeedController::class, '__construct')]
#[CoversMethod(FeedController::class, 'atom')]
#[CoversMethod(FeedController::class, 'withEtag')]
#[CoversMethod(FeedController::class, 'rss')]
#[CoversMethod(FeedController::class, 'ical')]
#[CoversMethod(FeedController::class, 'getShifts')]
#[CoversMethod(FeedController::class, 'shifts')]
#[CoversMethod(FeedController::class, 'getNews')]
#[AllowMockObjectsWithoutExpectations]
class FeedControllerTest extends ControllerTestCase
{
    protected Authenticator $auth;
    protected UrlGenerator&MockObject $url;

    public function testAtom(): void
    {
        $controller = new FeedController($this->auth, $this->request, $this->response, $this->url);

        $this->setExpects(
            $this->response,
            'withHeader',
            ['content-type', 'application/atom+xml; charset=utf-8'],
            $this->response
        );
        $this->response->expects($this->once())
            ->method('setEtag')
            ->willReturnCallback(function ($etag) {
                $this->assertNotEmpty($etag);
                return $this->response;
            });
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('api/atom', $view);
                $this->assertArrayHasKey('news', $data);

                return $this->response;
            });

        $controller->atom();
    }

    public function testRss(): void
    {
        $controller = new FeedController($this->auth, $this->request, $this->response, $this->url);

        $this->setExpects(
            $this->response,
            'withHeader',
            ['content-type', 'application/rss+xml; charset=utf-8'],
            $this->response
        );
        $this->setExpects($this->response, 'setEtag', null, $this->response);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('api/rss', $view);
                $this->assertArrayHasKey('news', $data);

                return $this->response;
            });

        $controller->rss();
    }

    public function testIcal(): void
    {
        $this->request = $this->request->withQueryParams(['key' => 'fo0']);
        $this->auth = new Authenticator(
            $this->request,
            new Session(new MockArraySessionStorage()),
            new User(),
        );
        $controller = new FeedController($this->auth, $this->request, $this->response, $this->url);

        /** @var User $user */
        $user = User::factory()->create(['api_key' => 'fo0']);
        ShiftEntry::factory(3)->create(['user_id' => $user->id]);

        $matcher = $this->exactly(2);
        $this->response->expects($matcher)
            ->method('withHeader')->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('content-type', $parameters[0]);
                    $this->assertSame('text/calendar; charset=utf-8', $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('content-disposition', $parameters[0]);
                    $this->assertSame('attachment; filename=shifts.ics', $parameters[1]);
                }
                return $this->response;
            });

        $this->setExpects($this->response, 'setEtag', null, $this->response);

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

    public function testIcalEmpty(): void
    {
        $this->request = $this->request->withQueryParams(['key' => 'fo0']);
        $this->auth = new Authenticator(
            $this->request,
            new Session(new MockArraySessionStorage()),
            new User(),
        );
        $controller = new FeedController($this->auth, $this->request, $this->response, $this->url);

        User::factory()->create(['api_key' => 'fo0']);

        $matcher = $this->exactly(2);
        $this->response->expects($matcher)
            ->method('withHeader')->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('content-type', $parameters[0]);
                    $this->assertSame('text/calendar; charset=utf-8', $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('content-disposition', $parameters[0]);
                    $this->assertSame('attachment; filename=shifts.ics', $parameters[1]);
                }
                return $this->response;
            });

        $this->setExpects($this->response, 'setEtag', null, $this->response);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('api/ical', $view);
                $this->assertArrayHasKey('shiftEntries', $data);

                /** @var ShiftEntry[]|Collection $shiftEntries */
                $shiftEntries = $data['shiftEntries'];
                $this->assertCount(0, $shiftEntries);

                return $this->response;
            });

        $controller->ical();
    }

    public function testShifts(): void
    {
        $this->request = $this->request->withQueryParams(['key' => 'fo0']);
        $this->auth = new Authenticator(
            $this->request,
            new Session(new MockArraySessionStorage()),
            new User(),
        );
        $controller = new FeedController($this->auth, $this->request, $this->response, $this->url);
        $this->setExpects($this->url, 'to', null, 'https://host/shift/1337', $this->atLeastOnce());

        /** @var User $user */
        $user = User::factory()->create(['api_key' => 'fo0']);
        ShiftEntry::factory(3)->create(['user_id' => $user->id]);

        $this->setExpects(
            $this->response,
            'withAddedHeader',
            ['content-type', 'application/json; charset=utf-8'],
            $this->response
        );

        $this->setExpects($this->response, 'setEtag', null, $this->response);

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
                        'SID', 'shifttype_id', 'URL', 'link',
                        'shifttype_name', 'shifttype_description',
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


    public static function getNewsMeetingsDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    #[DataProvider('getNewsMeetingsDataProvider')]
    public function testGetNewsMeetings(bool $isMeeting): void
    {
        $controller = new FeedController($this->auth, $this->request, $this->response, $this->url);

        $this->request->attributes->set('meetings', $isMeeting);
        $this->setExpects($this->response, 'withHeader', null, $this->response);
        $this->setExpects($this->response, 'setEtag', null, $this->response);
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

    public function testGetNewsLimit(): void
    {
        News::query()->where('id', '<>', 1)->update(['updated_at' => Carbon::now()->subHour()]);
        $controller = new FeedController($this->auth, $this->request, $this->response, $this->url);

        $this->setExpects($this->response, 'withHeader', null, $this->response);
        $this->setExpects($this->response, 'setEtag', null, $this->response);
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

        $this->config->set([
            'display_news' => 10,
            'timezone' => 'UTC',
        ]);
        $this->auth = $this->createMock(Authenticator::class);
        $this->url = $this->createMock(UrlGenerator::class);

        News::factory(7)->create(['is_meeting' => false]);
        News::factory(5)->create(['is_meeting' => true]);
    }
}
