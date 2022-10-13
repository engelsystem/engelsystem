<?php

namespace Engelsystem\Test\Unit\Controllers;

use Carbon\Carbon;
use Engelsystem\Config\Config;
use Engelsystem\Test\Unit\Controllers\Stub\ChecksArrivalsAndDeparturesImplementation;
use Engelsystem\Test\Unit\TestCase;

class ChecksArrivalsAndDeparturesTest extends TestCase
{
    public function invalidDateCombinations(): array
    {
        return [
            [null, null, '2022-01-16', '2022-01-15'],                 # arrival greater than departure
            ['2022-01-15', '2022-01-15', '2022-01-14', '2022-01-16'], # arrival before buildup, departure after teardown
        ];
    }

    public function validDateCombinations(): array
    {
        return [
            [null, null, '2022-01-15', '2022-01-15'],                 # arrival equals departure
            [null, null, '2022-01-14', '2022-01-15'],                 # arrival smaller than departure
            ['2022-01-14', null, '2022-01-14', '2022-01-15'],         # arrival on buildup
            ['2022-01-13', null, '2022-01-14', '2022-01-15'],         # arrival after buildup
            [null, '2022-01-15', '2022-01-14', '2022-01-15'],         # departure on teardown
            [null, '2022-01-16', '2022-01-14', '2022-01-15'],         # departure before teardown
            ['2022-01-14', '2022-01-16', '2022-01-14', '2022-01-15'], # all together
        ];
    }

    /**
     * @covers \Engelsystem\Controllers\ChecksArrivalsAndDepartures::isArrivalDateValid
     * @dataProvider invalidDateCombinations
     */
    public function testCheckInvalidDatesForArrival($buildup, $teardown, $arrival, $departure)
    {
        config(['buildup_start' => is_null($buildup) ? null: new Carbon($buildup)]);
        config(['teardown_end' => is_null($teardown) ? null: new Carbon($teardown)]);

        $check = new ChecksArrivalsAndDeparturesImplementation();
        $this->assertFalse($check->checkArrival($arrival, $departure));
    }

    /**
     * @covers \Engelsystem\Controllers\ChecksArrivalsAndDepartures::isDepartureDateValid
     * @dataProvider invalidDateCombinations
     */
    public function testCheckInvalidDatesForDeparture($buildup, $teardown, $arrival, $departure)
    {
        config(['buildup_start' => is_null($buildup) ? null: new Carbon($buildup)]);
        config(['teardown_end' => is_null($teardown) ? null: new Carbon($teardown)]);

        $check = new ChecksArrivalsAndDeparturesImplementation();
        $this->assertFalse($check->checkDeparture($arrival, $departure));
    }

    /**
     * @covers \Engelsystem\Controllers\ChecksArrivalsAndDepartures::isArrivalDateValid
     * @dataProvider validDateCombinations
     */
    public function testCheckValidDatesForArrival($buildup, $teardown, $arrival, $departure)
    {
        config(['buildup_start' => is_null($buildup) ? null: new Carbon($buildup)]);
        config(['teardown_end' => is_null($teardown) ? null: new Carbon($teardown)]);

        $check = new ChecksArrivalsAndDeparturesImplementation();
        $this->assertTrue($check->checkArrival($arrival, $departure));
    }

    /**
     * @covers \Engelsystem\Controllers\ChecksArrivalsAndDepartures::isDepartureDateValid
     * @dataProvider validDateCombinations
     */
    public function testCheckValidDatesForDeparture($buildup, $teardown, $arrival, $departure)
    {
        config(['buildup_start' => is_null($buildup) ? null: new Carbon($buildup)]);
        config(['teardown_end' => is_null($teardown) ? null: new Carbon($teardown)]);

        $check = new ChecksArrivalsAndDeparturesImplementation();
        $this->assertTrue($check->checkDeparture($arrival, $departure));
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->config = new Config();
        $this->app->instance('config', $this->config);
        $this->app->instance(Config::class, $this->config);
    }
}
