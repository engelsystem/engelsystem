<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Validation\Rules;

use Engelsystem\Http\Validation\Rules\Truthy;
use Engelsystem\Test\Unit\TestCase;

class TruthyTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Validation\Rules\Truthy
     */
    public function testValidate(): void
    {
        $rule = new class () {
            use Truthy;

            public function validate(mixed $value): bool
            {
                return $this->truthy($value);
            }
        };

        $this->assertTrue($rule->validate('yes'));
        $this->assertTrue($rule->validate('on'));
        $this->assertTrue($rule->validate(1));
        $this->assertTrue($rule->validate('1'));
        $this->assertTrue($rule->validate('true'));
        $this->assertTrue($rule->validate(true));

        $this->assertFalse($rule->validate('no'));
        $this->assertFalse($rule->validate('off'));
        $this->assertFalse($rule->validate(0));
        $this->assertFalse($rule->validate('0'));
        $this->assertFalse($rule->validate('false'));
        $this->assertFalse($rule->validate(false));
        $this->assertFalse($rule->validate(null));
    }
}
