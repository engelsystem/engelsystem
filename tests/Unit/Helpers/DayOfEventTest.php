<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Helpers\DayOfEvent;
use Engelsystem\Test\Unit\ServiceProviderTest;

class DayOfEventTest extends ServiceProviderTest
{
    private const FORMAT = 'Y-m-d H:i:s';
    private Config $config;

    public function setUp(): void
    {
        $app = $this->createAndSetUpAppWithConfig([]);
        $this->config = $app->get('config');
    }

    public function tearDown(): void
    {
        Carbon::setTestNow();
    }

    /**
     * @return Array<string, array>
     */
    public function provideTestGetData(): array
    {
        return [
            'day -2 (10:00, with day 0)' => [-2, true, '2023-07-31 15:23:42', '2023-07-28 10:00:00'],
            'day -2 (23:59, with day 0)' => [-2, true, '2023-07-31 15:23:42', '2023-07-28 23:59:59'],
            'day -1 (23:59, with day 0)' => [-1, true, '2023-07-31 15:23:42', '2023-07-29 23:59:59'],
            'day 0 (with day 0)'         => [0, true, '2023-07-31 15:23:42', '2023-07-30 16:00:00'],
            'day 1 (with day 0)'         => [1, true, '2023-07-31 15:23:42', '2023-07-31 10:00:00'],
            'day 2 (with day 0)'         => [2, true, '2023-07-31 15:23:42', '2023-08-01 10:00:00'],

            'day -2 (without day 0)' => [-2, false, '2023-07-31 15:23:42', '2023-07-29 10:00:00'],
            'day -1 (without day 0)' => [-1, false, '2023-07-31 15:23:42', '2023-07-30 23:59:59'],
            'day 1 (without day 0)'  => [1, false, '2023-07-31 15:23:42', '2023-07-31 00:00:00'],
            'day 2 (without day 0)'  => [2, false, '2023-07-31 15:23:42', '2023-08-01 16:00:00'],

            'no start date' => [null, false, null, '2023-08-01 16:00:00'],
        ];
    }

    /**
     * @dataProvider provideTestGetData
     * @covers \Engelsystem\Helpers\DayOfEvent
     */
    public function testGet(
        int | null $expected,
        bool $eventHasDay0,
        string | null $eventStart,
        string $now
    ): void {
        $this->config->set(
            'event_start',
            $eventStart ? Carbon::createFromFormat(self::FORMAT, $eventStart) : null
        );
        $this->config->set('event_has_day0', $eventHasDay0);
        Carbon::setTestNow(Carbon::createFromFormat(self::FORMAT, $now));
        $this->assertSame($expected, DayOfEvent::get());
    }
}
