<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Validation;

use Engelsystem\Http\Validation\Validator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Validation\Validator::validate
     * @covers \Engelsystem\Http\Validation\Validator::getData
     * @covers \Engelsystem\Http\Validation\Validator::getErrors
     * @covers \Engelsystem\Http\Validation\Validator::configureValidationFactory
     */
    public function testValidate(): void
    {
        $val = new Validator();

        $this->assertTrue($val->validate(
            ['test' => '', 'foo' => 'bar', 'lorem' => 'on', 'dolor' => 'bla'],
            ['test' => 'optional', 'lorem' => 'accepted']
        ));
        $this->assertEquals(['test' => null, 'lorem' => 'on'], $val->getData());

        $this->assertFalse($val->validate(
            [],
            ['lorem' => 'required|min:3']
        ));
        $this->assertEquals(
            ['lorem' => ['validation.lorem.required', 'validation.lorem.min']],
            $val->getErrors()
        );
        $this->assertFalse($val->validate(
            ['lorem' => 'X'],
            ['lorem' => 'required|min:3']
        ));
        $this->assertFalse($val->validate(
            ['lorem' => 'X'],
            ['lorem' => ['required', 'min:3']]
        ));
        $this->assertFalse($val->validate(
            ['lorem' => 'X'],
            ['lorem' => ['required', ['min', '3']]]
        ));
        $this->assertEquals(
            ['lorem' => ['validation.lorem.min']],
            $val->getErrors()
        );
        $this->assertEquals(
            [],
            $val->getData()
        );
    }

    /**
     * @covers \Engelsystem\Http\Validation\Validator::validate
     */
    public function testValidateChaining(): void
    {
        $val = new Validator();

        $this->assertTrue($val->validate(
            ['lorem' => 10],
            ['lorem' => 'required|min:3|max:10']
        ));
        $this->assertTrue($val->validate(
            ['lorem' => 3],
            ['lorem' => 'required|min:3|max:10']
        ));

        $this->assertFalse($val->validate(
            ['lorem' => 'OMG'],
            ['lorem' => 'required|min:4|max:10']
        ));
        $this->assertEquals(['lorem' => ['validation.lorem.min']], $val->getErrors());
        $this->assertFalse($val->validate(
            ['lorem' => 42],
            ['lorem' => 'required|min:3|max:10']
        ));
    }

    /**
     * @covers \Engelsystem\Http\Validation\Validator::validate
     */
    public function testValidateMultipleParameters(): void
    {
        $val = new Validator();

        $this->assertFalse($val->validate(
            ['lorem' => 'h'],
            ['lorem' => 'length:2:3']
        ));
        $this->assertTrue($val->validate(
            ['lorem' => 'hey'],
            ['lorem' => 'length:2:3']
        ));
        $this->assertFalse($val->validate(
            ['lorem' => 'heyy'],
            ['lorem' => 'length:2:3']
        ));
    }

    /**
     * @covers \Engelsystem\Http\Validation\Validator::validate
     */
    public function testValidateNotImplemented(): void
    {
        $val = new Validator();

        $this->expectException(InvalidArgumentException::class);

        $val->validate(
            ['lorem' => 'bar'],
            ['foo' => 'never_implemented']
        );
    }

    /**
     * @covers \Engelsystem\Http\Validation\Validator::map
     * @covers \Engelsystem\Http\Validation\Validator::mapBack
     */
    public function testValidateMapping(): void
    {
        $val = new Validator();

        $this->assertTrue($val->validate(
            ['foo' => 'bar'],
            ['foo' => 'required']
        ));
        $this->assertTrue($val->validate(
            ['foo' => '0'],
            ['foo' => 'int']
        ));
        $this->assertFalse($val->validate(
            ['foo' => '0.0'],
            ['foo' => 'int']
        ));
        $this->assertTrue($val->validate(
            ['foo' => '0.0'],
            ['foo' => 'float']
        ));
        $this->assertTrue($val->validate(
            ['foo' => 'on'],
            ['foo' => 'accepted']
        ));
        $this->assertTrue($val->validate(
            ['foo' => null],
            ['foo' => 'optional']
        ));
        $this->assertTrue($val->validate(
            ['foo' => ''],
            ['foo' => 'optional']
        ));
        $this->assertEquals(['foo' => null], $val->getData());
        $this->assertTrue($val->validate(
            ['foo' => 'bar'],
            ['foo' => 'optional']
        ));
        $this->assertEquals(['foo' => 'bar'], $val->getData());

        $this->assertFalse($val->validate(
            [],
            ['lorem' => 'required']
        ));
        $this->assertEquals(
            ['lorem' => ['validation.lorem.required']],
            $val->getErrors()
        );
    }

    /**
     * @covers \Engelsystem\Http\Validation\Validator::validate
     */
    public function testValidateNullable(): void
    {
        $val = new Validator();

        $this->assertTrue($val->validate(
            [],
            ['foo' => 'nullable']
        ));
        $this->assertTrue($val->validate(
            ['foo' => null],
            ['foo' => 'nullable']
        ));
        $this->assertEquals(['foo' => null], $val->getData());
        $this->assertTrue($val->validate(
            ['foo' => ''],
            ['foo' => 'nullable']
        ));
        $this->assertEquals(['foo' => null], $val->getData());
        $this->assertTrue($val->validate(
            ['foo' => 'bar'],
            ['foo' => 'nullable']
        ));
        $this->assertEquals(['foo' => 'bar'], $val->getData());
        $this->assertTrue($val->validate(
            ['foo' => null],
            ['foo' => 'nullable|min:42']
        ));
        $this->assertTrue($val->validate(
            ['foo' => ''],
            ['foo' => 'nullable|min:42']
        ));
        $this->assertEquals(['foo' => null], $val->getData());
        $this->assertTrue($val->validate(
            ['foo' => '99'],
            ['foo' => 'nullable|int']
        ));
        $this->assertEquals(['foo' => '99'], $val->getData());

        $this->assertFalse($val->validate(
            ['foo' => 'foo'],
            ['foo' => 'nullable|min:42']
        ));
        $this->assertEquals([], $val->getData());
        $this->assertFalse($val->validate(
            ['foo' => 'T'],
            ['foo' => 'optional|int']
        ));
        $this->assertEquals([], $val->getData());
    }

    /**
     * @covers \Engelsystem\Http\Validation\Validator::addErrors
     */
    public function testAddErrors(): void
    {
        $val = new Validator();
        $val->addErrors(['bar' => ['Lorem']]);
        $val->addErrors(['foo' => ['Foo value is definitely wrong!']]);

        $this->assertEquals([
            'bar' => ['Lorem'],
            'foo' => ['Foo value is definitely wrong!'],
        ], $val->getErrors());
    }
}
