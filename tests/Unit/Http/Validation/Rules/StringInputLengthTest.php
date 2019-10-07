<?php

namespace Engelsystem\Test\Unit\Http\Validation\Rules;

use Engelsystem\Test\Unit\Http\Validation\Rules\Stub\UsesStringInputLength;
use Engelsystem\Test\Unit\TestCase;

class StringInputLengthTest extends TestCase
{
    /**
     * @covers       \Engelsystem\Http\Validation\Rules\StringInputLength::validate
     * @covers       \Engelsystem\Http\Validation\Rules\StringInputLength::isDateTime
     * @dataProvider validateProvider
     * @param mixed $input
     * @param mixed $expectedInput
     */
    public function testValidate($input, $expectedInput)
    {
        $rule = new UsesStringInputLength();
        $rule->validate($input);

        $this->assertEquals($expectedInput, $rule->lastInput);
    }

    /**
     * @return array[]
     */
    public function validateProvider()
    {
        return [
            ['TEST', 4],
            ['?', 1],
            ['2042-01-01 00:00', '2042-01-01 00:00'],
            ['3', '3'],
        ];
    }
}
