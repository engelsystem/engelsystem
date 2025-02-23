<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Validation\Rules;

use Engelsystem\Http\Validation\Rules\Checked;
use Engelsystem\Test\Unit\TestCase;

class CheckedTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Validation\Rules\Checked::isValid
     * @see TruthyTest
     */
    public function testIsValid(): void
    {
        $rule = new Checked();

        $this->assertTrue($rule->isValid('on'));
        $this->assertFalse($rule->isValid(null));
    }
}
