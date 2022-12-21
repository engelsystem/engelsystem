<?php

namespace Engelsystem\Test\Unit\Http\Validation\Rules;

use Engelsystem\Http\Validation\Rules\Max;
use Engelsystem\Test\Unit\TestCase;

class MaxTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Validation\Rules\Max
     */
    public function testValidate(): void
    {
        $rule = new Max(3);
        $this->assertFalse($rule->validate(10));
        $this->assertFalse($rule->validate('22'));
        $this->assertTrue($rule->validate(3));
        $this->assertFalse($rule->validate('TEST'));
        $this->assertTrue($rule->validate('AS'));

        $rule = new Max('2042-01-01');
        $this->assertFalse($rule->validate('2100-01-01'));
        $this->assertTrue($rule->validate('2000-01-01'));
    }
}
