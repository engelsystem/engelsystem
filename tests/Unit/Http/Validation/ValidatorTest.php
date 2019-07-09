<?php

namespace Engelsystem\Test\Unit\Http\Validation;

use Engelsystem\Http\Validation\Validates;
use Engelsystem\Http\Validation\Validator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Validation\Validator::__construct
     * @covers \Engelsystem\Http\Validation\Validator::validate
     * @covers \Engelsystem\Http\Validation\Validator::getData
     * @covers \Engelsystem\Http\Validation\Validator::getErrors
     */
    public function testValidate()
    {
        $val = new Validator(new Validates);
        $this->assertTrue($val->validate(
            ['foo' => 'bar', 'lorem' => 'on'],
            ['foo' => 'required|not_in:lorem,ipsum,dolor', 'lorem' => 'accepted']
        ));
        $this->assertEquals(['foo' => 'bar', 'lorem' => 'on'], $val->getData());

        $this->assertFalse($val->validate(
            [],
            ['lorem' => 'required|min:3']
        ));
        $this->assertEquals(
            ['lorem' => ['validation.lorem.required', 'validation.lorem.min']],
            $val->getErrors()
        );
    }

    /**
     * @covers \Engelsystem\Http\Validation\Validator::validate
     */
    public function testValidateNotImplemented()
    {
        $val = new Validator(new Validates);
        $this->expectException(InvalidArgumentException::class);

        $val->validate(
            ['lorem' => 'bar'],
            ['foo' => 'never_implemented']
        );
    }
}
