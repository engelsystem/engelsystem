<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Helpers\Carbon;
use PHPUnit\Framework\TestCase;
use Traversable;

class CarbonTest extends TestCase
{
    public function validDates(): Traversable
    {
        $format = '!Y-m-d H:i';
        yield '2022-04-16T10:44' => ['2022-04-16T10:44', Carbon::createFromFormat($format, '2022-04-16 10:44')];
        yield '2022-04-16 10:44' => ['2022-04-16T10:44', Carbon::createFromFormat($format, '2022-04-16 10:44')];
        yield '2020-12-24T13:37' => ['2020-12-24T13:37', Carbon::createFromFormat($format, '2020-12-24 13:37')];
        yield '2020-12-24 13:37' => ['2020-12-24T13:37', Carbon::createFromFormat($format, '2020-12-24 13:37')];
    }

    public function invalidDates(): Traversable
    {
        yield '202-12-12 11:11' => ['202-12-12T11:11'];
        yield '2022-23-24' => ['2022-23-24'];
        yield '16.04.2022 11:24' => ['16.04.2022 11:24'];
    }

    /**
     * @covers \Engelsystem\Helpers\Carbon::createFromDatetime
     * @dataProvider validDates
     */
    public function testCreateFromValidDatetime(string $value, Carbon $expected): void
    {
        $date = Carbon::createFromDatetime($value);
        self::assertSame($expected->timestamp, $date->timestamp);
    }

    /**
     * @covers \Engelsystem\Helpers\Carbon::createFromDatetime
     * @dataProvider invalidDates
     */
    public function testCreateFromInvalidDatetime(string $value): void
    {
        $date = Carbon::createFromDatetime($value);
        self::assertNull($date);
    }
}
