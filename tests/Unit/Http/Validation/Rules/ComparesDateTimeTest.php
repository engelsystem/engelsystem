<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Validation\Rules;

use Engelsystem\Helpers\Carbon;
use Engelsystem\Http\Validation\Rules\ComparesDateTime;
use Engelsystem\Test\Unit\Http\Validation\Rules\Stub\UsesComparesDateTime;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(ComparesDateTime::class, '__construct')]
#[CoversMethod(ComparesDateTime::class, 'validate')]
#[CoversMethod(ComparesDateTime::class, 'toDateTime')]
class ComparesDateTimeTest extends TestCase
{
    public function testValidate(): void
    {
        $rule = new UsesComparesDateTime('2024-01-02 13:37');
        $rule->setCallback(function ($input, $comparison) {
            /** @var Carbon $input */
            $this->assertInstanceOf(Carbon::class, $input);
            $this->assertEquals('2042-10-11 00:00:00', $input->toDateTimeString());

            $this->assertInstanceOf(Carbon::class, $comparison);
            $this->assertEquals('2024-01-02 13:37:00', $comparison->toDateTimeString());

            return true;
        });

        $this->assertTrue($rule->validate('2042-10-11'));
    }

    public function testCompareTo(): void
    {
        $rule = new UsesComparesDateTime('2024-01-02 13:37');

        /** @var Carbon $comparisonTarget */
        $comparisonTarget = $rule->getCompareTo();
        $this->assertInstanceOf(Carbon::class, $comparisonTarget);
        $this->assertEquals('2024-01-02 13:37:00', $comparisonTarget->toDateTimeString());
    }

    public function testOrEqual(): void
    {
        $rule = new UsesComparesDateTime('2024-01-02 13:37');
        $this->assertFalse($rule->getOrEqual());

        $rule = new UsesComparesDateTime('2024-01-02 13:37', true);
        $this->assertTrue($rule->getOrEqual());
    }

    public function testValidateDateTimeStaysSame(): void
    {
        $a = Carbon::now();
        $b = Carbon::now();

        $rule = new UsesComparesDateTime($a);
        $rule->setCallback(function ($input, $comparison) use ($a, $b) {
            $this->assertEquals($b, $input);
            $this->assertEquals($a, $comparison);

            return false;
        });

        $this->assertEquals($a, $rule->getCompareTo());
        $this->assertFalse($rule->validate($b));
    }
}
