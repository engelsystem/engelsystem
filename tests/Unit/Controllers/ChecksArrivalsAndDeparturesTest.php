<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Carbon\Carbon;
use Engelsystem\Config\Config;
use Engelsystem\Controllers\ChecksArrivalsAndDepartures;
use Engelsystem\Test\Unit\Controllers\Stub\ChecksArrivalsAndDeparturesImplementation;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversMethod(ChecksArrivalsAndDepartures::class, 'isArrivalDateValid')]
#[CoversMethod(ChecksArrivalsAndDepartures::class, 'toCarbon')]
#[CoversMethod(ChecksArrivalsAndDepartures::class, 'isBeforeBuildup')]
#[CoversMethod(ChecksArrivalsAndDepartures::class, 'isAfterTeardown')]
#[CoversMethod(ChecksArrivalsAndDepartures::class, 'isDepartureDateValid')]
class ChecksArrivalsAndDeparturesTest extends TestCase
{
    public static function invalidArrivalCombinations(): array
    {
        return [
            [null, null, null, null],                 # arrival being null
            [null, null, '2022-01-16', '2022-01-15'], # arrival greater than departure
            ['2022-01-15', null, '2022-01-14', null], # arrival before buildup
            [null, '2022-01-14', '2022-01-15', null], # arrival after teardown
        ];
    }

    public static function invalidDepartureCombinations(): array
    {
        return [
            [null, null, '2022-01-16', '2022-01-15'], # departure smaller than arrival
            ['2022-01-15', null, null, '2022-01-14'], # departure before buildup
            [null, '2022-01-14', null, '2022-01-15'], # departure after teardown
        ];
    }

    public static function validArrivalCombinations(): array
    {
        return [
            [null, null, '2022-01-15', '2022-01-15'],                 # arrival equals departure
            [null, null, '2022-01-14', '2022-01-15'],                 # arrival smaller than departure
            ['2022-01-14', null, '2022-01-14', '2022-01-15'],         # arrival on buildup
            ['2022-01-13', null, '2022-01-14', '2022-01-15'],         # arrival after buildup
        ];
    }

    public static function validDepartureCombinations(): array
    {
        return [
            [null, null, '2022-01-15', null],                         # departure being null
            [null, null, '2022-01-15', '2022-01-15'],                 # departure equals arrival
            [null, null, '2022-01-14', '2022-01-15'],                 # departure greater than arrival
            [null, '2022-01-15', '2022-01-14', '2022-01-15'],         # departure on teardown
            [null, '2022-01-16', '2022-01-14', '2022-01-15'],         # departure before teardown
        ];
    }

    #[DataProvider('invalidArrivalCombinations')]
    public function testCheckInvalidDatesForArrival(
        ?string $buildup,
        ?string $teardown,
        ?string $arrival,
        ?string $departure
    ): void {
        config(['buildup_start' => is_null($buildup) ? null : new Carbon($buildup)]);
        config(['teardown_end' => is_null($teardown) ? null : new Carbon($teardown)]);

        $check = new ChecksArrivalsAndDeparturesImplementation();
        $this->assertFalse($check->checkArrival($arrival, $departure));
    }

    #[DataProvider('invalidDepartureCombinations')]
    public function testCheckInvalidDatesForDeparture(
        ?string $buildup,
        ?string $teardown,
        ?string $arrival,
        ?string $departure
    ): void {
        config(['buildup_start' => is_null($buildup) ? null : new Carbon($buildup)]);
        config(['teardown_end' => is_null($teardown) ? null : new Carbon($teardown)]);

        $check = new ChecksArrivalsAndDeparturesImplementation();
        $this->assertFalse($check->checkDeparture($arrival, $departure));
    }

    #[DataProvider('validArrivalCombinations')]
    public function testCheckValidDatesForArrival(
        ?string $buildup,
        ?string $teardown,
        ?string $arrival,
        ?string $departure
    ): void {
        config(['buildup_start' => is_null($buildup) ? null : new Carbon($buildup)]);
        config(['teardown_end' => is_null($teardown) ? null : new Carbon($teardown)]);

        $check = new ChecksArrivalsAndDeparturesImplementation();
        $this->assertTrue($check->checkArrival($arrival, $departure));
    }

    #[DataProvider('validDepartureCombinations')]
    public function testCheckValidDatesForDeparture(
        ?string $buildup,
        ?string $teardown,
        ?string $arrival,
        ?string $departure
    ): void {
        config(['buildup_start' => is_null($buildup) ? null : new Carbon($buildup)]);
        config(['teardown_end' => is_null($teardown) ? null : new Carbon($teardown)]);

        $check = new ChecksArrivalsAndDeparturesImplementation();
        $this->assertTrue($check->checkDeparture($arrival, $departure));
    }

    public function setUp(): void
    {
        parent::setUp();
        $onfig = new Config();
        $this->app->instance('config', $onfig);
        $this->app->instance(Config::class, $onfig);
    }
}
