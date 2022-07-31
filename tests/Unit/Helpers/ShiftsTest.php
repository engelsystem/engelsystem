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
    public function testIsNightShift()
    {
        $config = new Config(['night_shifts' => [
            'enabled'    => false,
            'start'      => 2,
            'end'        => 6,
            'multiplier' => 2,
        ]]);
        $this->app->instance('config', $config);

        // At night but disabled
        $this->assertFalse(Shifts::isNightShift(
            new Carbon('2042-01-01 04:00'),
            new Carbon('2042-01-01 05:00')
        ));

        $config->set('night_shifts', array_merge($config->get('night_shifts'), ['enabled' => true]));

        // Is night shift
        $this->assertTrue(Shifts::isNightShift(
            new Carbon('2042-01-01 04:00'),
            new Carbon('2042-01-01 05:00')
        ));

        // Starts as night shift
        $this->assertTrue(Shifts::isNightShift(
            new Carbon('2042-01-01 05:45'),
            new Carbon('2042-01-01 07:00')
        ));

        // Ends as night shift
        $this->assertTrue(Shifts::isNightShift(
            new Carbon('2042-01-01 00:00'),
            new Carbon('2042-01-01 02:15')
        ));

        // Too early
        $this->assertFalse(Shifts::isNightShift(
            new Carbon('2042-01-01 00:00'),
            new Carbon('2042-01-01 01:59')
        ));

        // Too late
        $this->assertFalse(Shifts::isNightShift(
            new Carbon('2042-01-01 06:00'),
            new Carbon('2042-01-01 09:59')
        ));
    }

    /**
     * @covers \Engelsystem\Helpers\Shifts::getNightShiftMultiplier
     */
    public function testGetNightShiftMultiplier()
    {
        $config = new Config(['night_shifts' => [
            'enabled'    => true,
            'start'      => 2,
            'end'        => 6,
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
