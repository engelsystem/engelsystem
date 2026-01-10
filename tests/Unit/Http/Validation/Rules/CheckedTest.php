<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Validation\Rules;

use Engelsystem\Http\Validation\Rules\Checked;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Checked::class, 'isValid')]
class CheckedTest extends TestCase
{
    /**
     * @see TruthyTest
     */
    public function testIsValid(): void
    {
        $rule = new Checked();

        $this->assertTrue($rule->isValid('on'));
        $this->assertTrue($rule->isValid('yes'));
        $this->assertTrue($rule->isValid('1'));
        $this->assertTrue($rule->isValid(1));
        $this->assertTrue($rule->isValid('true'));
        $this->assertTrue($rule->isValid(true));

        $this->assertFalse($rule->isValid(null));
        $this->assertFalse($rule->isValid(''));
        $this->assertFalse($rule->isValid('off'));
        $this->assertFalse($rule->isValid('no'));
        $this->assertFalse($rule->isValid('0'));
        $this->assertFalse($rule->isValid(0));
        $this->assertFalse($rule->isValid('false'));
        $this->assertFalse($rule->isValid(false));
    }
}
