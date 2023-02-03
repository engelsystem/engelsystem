<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Validation\Rules;

use Engelsystem\Test\Unit\Http\Validation\Rules\Stub\UsesStringInputLength;
use Engelsystem\Test\Unit\TestCase;

class StringInputLengthTest extends TestCase
{
    /**
     * @covers       \Engelsystem\Http\Validation\Rules\StringInputLength::validate
     * @covers       \Engelsystem\Http\Validation\Rules\StringInputLength::isDateTime
     * @dataProvider validateProvider
     */
    public function testValidate(mixed $input, mixed $expectedInput): void
    {
        $rule = new UsesStringInputLength();
        $rule->validate($input);

        $this->assertEquals($expectedInput, $rule->lastInput);
    }

    /**
     * @return array[]
     */
    public function validateProvider(): array
    {
        return [
            ['TEST', 4],
            ['?', 1],
            ['', 0],
            ['2042-01-01 00:00', '2042-01-01 00:00'],
            ['2042-01-01', '2042-01-01'],
            ['12:42', '12:42'],
            ['3', '3'],
            ['...', 3],
            ['Test Tester', 11],
            ['com', 3],
            ['Test', 4],
            ['H', 1],
            ['3', 3],
            [42, 42],
            [99.3, 99.3],
        ];
    }
}
