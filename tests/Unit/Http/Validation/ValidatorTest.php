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
     */
    public function testValidate(): void
    {
        $val = new Validator();

        $this->assertTrue($val->validate(
            ['foo' => 'bar', 'lorem' => 'on', 'dolor' => 'bla'],
            ['lorem' => 'accepted']
        ));
        $this->assertEquals(['lorem' => 'on'], $val->getData());

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
    public function testValidateNesting(): void
    {
        $val = new Validator();

        $this->assertTrue($val->validate(
            [],
            ['foo' => 'not|required']
        ));

        $this->assertTrue($val->validate(
            ['foo' => 'foo'],
            ['foo' => 'not|int']
        ));
        $this->assertFalse($val->validate(
            ['foo' => 1],
            ['foo' => 'not|int']
        ));

        $this->assertTrue($val->validate(
            [],
            ['foo' => 'optional|int']
        ));
        $this->assertTrue($val->validate(
            ['foo' => '33'],
            ['foo' => 'optional|int']
        ));
        $this->assertFalse($val->validate(
            ['foo' => 'T'],
            ['foo' => 'optional|int']
        ));
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
