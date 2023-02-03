<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Validation\Rules;

use Engelsystem\Http\Validation\Rules\Between;
use Engelsystem\Test\Unit\TestCase;

class BetweenTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Validation\Rules\Between
     */
    public function testValidate(): void
    {
        $rule = new Between(3, 10);
        $this->assertFalse($rule->validate(1));
        $this->assertFalse($rule->validate('11'));
        $this->assertTrue($rule->validate(5));
        $this->assertFalse($rule->validate('AS'));
        $this->assertFalse($rule->validate('TestContentThatCounts'));
        $this->assertTrue($rule->validate('TESTING'));

        $rule = new Between('2042-01-01', '2042-10-10');
        $this->assertFalse($rule->validate('2000-01-01'));
        $this->assertFalse($rule->validate('3000-01-01'));
        $this->assertTrue($rule->validate('2042-05-11'));
    }
}
