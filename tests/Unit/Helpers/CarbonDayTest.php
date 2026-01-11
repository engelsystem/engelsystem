<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Helpers\CarbonDay;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Traversable;

#[CoversMethod(CarbonDay::class, '__construct')]
#[CoversMethod(CarbonDay::class, 'createFromDay')]
class CarbonDayTest extends TestCase
{
    public static function validDates(): Traversable
    {
        $format = 'Y-m-d H:i:s';
        yield '2025-09-23 00:00:00' => ['2025-09-23', CarbonDay::createFromFormat($format, '2025-09-23 00:00:00')];
        yield '2025-11-10 23:59:59' => ['2025-11-10', CarbonDay::createFromFormat($format, '2025-11-10 23:59:59')];
        yield '2042-01-01 11:42:59' => ['2042-01-01', CarbonDay::createFromFormat($format, '2042-01-01 11:42:59')];
    }

    #[DataProvider('validDates')]
    public function testCreateFromValidDatetime(string $value, CarbonDay $expected): void
    {
        $timestamp = $expected->timestamp;
        $expected->setHour(0)->setMinute(0)->setSecond(0);
        $date = new CarbonDay('@' . $timestamp);
        self::assertSame($expected->timestamp, $date->timestamp);
        self::assertSame($value, (string) $date);
        self::assertSame($value, $date->jsonSerialize());
    }

    #[DataProvider('validDates')]
    public function testCreateFromDayFromValidDatetime(string $value, CarbonDay $expected): void
    {
        $expected->setHour(0)->setMinute(0)->setSecond(0);
        $date = CarbonDay::createFromDay($value);
        self::assertSame($expected->timestamp, $date->timestamp);
        self::assertSame($value, (string) $date);
        self::assertSame($value, $date->jsonSerialize());
    }
}
