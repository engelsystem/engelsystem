<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Helpers\Shifts;
use Engelsystem\Test\Unit\TestCase;

class ShiftsTest extends TestCase
{
    /**
     * @covers \Engelsystem\Helpers\Shifts::isNightShift
     */
    public function testIsNightShiftDisabled(): void
    {
        $config = new Config(['night_shifts' => [
            'enabled'    => false,
            'start'      => 2,
            'end'        => 8,
            'multiplier' => 2,
        ]]);
        $this->app->instance('config', $config);

        // At night but disabled
        $this->assertFalse(Shifts::isNightShift(
            new Carbon('2042-01-01 04:00'),
            new Carbon('2042-01-01 05:00')
        ));
    }

    /**
     * @return array{0: string, 1: string, 2: boolean}[]
     */
    public function nightShiftData(): array
    {
        // $start, $end, $isNightShift
        return [
            // Is night shift
            ['2042-01-01 04:00', '2042-01-01 05:00', true],
            // Is night shift
            ['2042-01-01 02:00', '2042-01-01 02:15', true],
            // Is night shift
            ['2042-01-01 07:45', '2042-01-01 08:00', true],
            // Starts as night shift
            ['2042-01-01 07:59', '2042-01-01 09:00', true],
            // Ends as night shift
            ['2042-01-01 00:00', '2042-01-01 02:01', true],
            // Equals night shift
            ['2042-01-01 02:00', '2042-01-01 08:00', true],
            // Contains night shift
            ['2042-01-01 01:00', '2042-01-01 09:00', true],
            // Too early
            ['2042-01-01 00:00', '2042-01-01 02:00', false],
            // Too late
            ['2042-01-01 08:00', '2042-01-01 10:00', false],
            // Out of range
            ['2042-01-01 23:00', '2042-01-02 01:00', false],
        ];
    }

    /**
     * @covers       \Engelsystem\Helpers\Shifts::isNightShift
     * @dataProvider nightShiftData
     */
    public function testIsNightShiftEnabled(string $start, string $end, bool $isNightShift): void
    {
        $config = new Config(['night_shifts' => [
            'enabled'    => true,
            'start'      => 2,
            'end'        => 8,
            'multiplier' => 2,
        ]]);
        $this->app->instance('config', $config);

        $this->assertEquals($isNightShift, Shifts::isNightShift(new Carbon($start), new Carbon($end)));
    }

    /**
     * @covers \Engelsystem\Helpers\Shifts::getNightShiftMultiplier
     */
    public function testGetNightShiftMultiplier(): void
    {
        $config = new Config(['night_shifts' => [
            'enabled'    => true,
            'start'      => 2,
            'end'        => 8,
            'multiplier' => 2,
        ]]);
        $this->app->instance('config', $config);

        $this->assertEquals(2, Shifts::getNightShiftMultiplier(
            new Carbon('2042-01-01 02:00'),
            new Carbon('2042-01-01 04:00')
        ));

        $config->set('night_shifts', array_merge($config->get('night_shifts'), ['enabled' => false]));
        $this->assertEquals(1, Shifts::getNightShiftMultiplier(
            new Carbon('2042-01-01 02:00'),
            new Carbon('2042-01-01 04:00')
        ));
    }
}
