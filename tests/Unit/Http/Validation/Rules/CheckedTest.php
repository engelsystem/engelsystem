<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Validation\Rules;

use Engelsystem\Http\Validation\Rules\Checked;
use Engelsystem\Test\Unit\TestCase;

class CheckedTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Validation\Rules\Checked::validate
     * @see TruthyTest
     */
    public function testValidate(): void
    {
        $rule = new Checked();

        $this->assertTrue($rule->validate('on'));
        $this->assertFalse($rule->validate(null));
    }
}
