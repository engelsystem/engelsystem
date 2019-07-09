<?php

namespace Engelsystem\Test\Unit\Http\Validation;

use Engelsystem\Http\Validation\Validates;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ValidatesTest extends TestCase
{
    /**
     * @return array
     */
    public function provideAccepted()
    {
        return [
            ['true'],
            ['1'],
            ['y'],
            ['yes'],
            ['on'],
            ['1test', false],
            ['false', false],
            ['no', false],
        ];
    }

    /**
     * @covers       \Engelsystem\Http\Validation\Validates::accepted
     * @param mixed $value
     * @param bool  $result
     * @dataProvider provideAccepted
     */
    public function testAccepted($value, bool $result = true)
    {
        $val = new Validates;
        $this->assertTrue($val->accepted($value) === $result);
    }

    /**
     * @return array
     */
    public function provideBetween()
    {
        return [
            ['42', [10, 100]],
            [42.5, [42, 43]],
            [42, [42, 1000]],
            [1337, [0, 99], false],
            [-17, [32, 45], false],
        ];
    }

    /**
     * @covers       \Engelsystem\Http\Validation\Validates::between
     * @param mixed $value
     * @param array $parameters
     * @param bool  $result
     * @dataProvider provideBetween
     */
    public function testBetween($value, array $parameters, bool $result = true)
    {
        $val = new Validates;
        $this->assertTrue($val->between($value, $parameters) === $result);
    }

    /**
     * @return array
     */
    public function provideBool()
    {
        return [
            ['1'],
            [1],
            [true],
            ['0'],
            [0],
            [false],
            ['true', false],
            ['false', false],
            ['yes', false],
            ['no', false],
            ['bool', false],
        ];
    }

    /**
     * @covers       \Engelsystem\Http\Validation\Validates::bool
     * @param mixed $value
     * @param bool  $result
     * @dataProvider provideBool
     */
    public function testBool($value, bool $result = true)
    {
        $val = new Validates;
        $this->assertTrue($val->bool($value) === $result);
    }

    /**
     * @return array
     */
    public function provideIn()
    {
        return [
            ['lorem', ['lorem,ipsum,dolor']],
            [99, ['66,77,88,99,111']],
            [4, ['1,3,5,7'], false],
            ['toggle', ['on,off'], false],
        ];
    }

    /**
     * @covers       \Engelsystem\Http\Validation\Validates::in
     * @param mixed $value
     * @param array $parameters
     * @param bool  $result
     * @dataProvider provideIn
     */
    public function testIn($value, array $parameters, bool $result = true)
    {
        $val = new Validates;
        $this->assertTrue($val->in($value, $parameters) === $result);
    }

    /**
     * @return array
     */
    public function provideInt()
    {
        return [
            ['1337'],
            [42],
            ['0'],
            [false, false],
            ['12asd1', false],
            ['one', false],
        ];
    }

    /**
     * @covers       \Engelsystem\Http\Validation\Validates::int
     * @param mixed $value
     * @param bool  $result
     * @dataProvider provideInt
     */
    public function testInt($value, bool $result = true)
    {
        $val = new Validates;
        $this->assertTrue($val->int($value) === $result);
    }

    /**
     * @return array
     */
    public function provideMax()
    {
        return [
            ['99', [100]],
            [-42, [1024]],
            [99, [99]],
            [100, [10], false],
        ];
    }

    /**
     * @covers       \Engelsystem\Http\Validation\Validates::max
     * @param mixed $value
     * @param array $parameters
     * @param bool  $result
     * @dataProvider provideMax
     */
    public function testMax($value, array $parameters, bool $result = true)
    {
        $val = new Validates;
        $this->assertTrue($val->max($value, $parameters) === $result);
    }

    /**
     * @return array
     */
    public function provideMin()
    {
        return [
            [32, [0]],
            [7, [7]],
            ['99', [10]],
            [3, [42], false],
        ];
    }

    /**
     * @covers       \Engelsystem\Http\Validation\Validates::min
     * @param mixed $value
     * @param array $parameters
     * @param bool  $result
     * @dataProvider provideMin
     */
    public function testMin($value, array $parameters, bool $result = true)
    {
        $val = new Validates;
        $this->assertTrue($val->min($value, $parameters) === $result);
    }

    /**
     * @return array
     */
    public function provideNotIn()
    {
        return [
            [77, ['50,60,70']],
            ['test', ['coding,deployment']],
            ['PHP', ['Java,PHP,bash'], false],
        ];
    }

    /**
     * @covers       \Engelsystem\Http\Validation\Validates::notIn
     * @param mixed $value
     * @param array $parameters
     * @param bool  $result
     * @dataProvider provideNotIn
     */
    public function testNotIn($value, array $parameters, bool $result = true)
    {
        $val = new Validates;
        $this->assertTrue($val->notIn($value, $parameters) === $result);
    }

    /**
     * @return array
     */
    public function provideNumeric()
    {
        return [
            [77],
            ['42'],
            ['1337e0'],
            ['123f00', false],
            [null, false],
        ];
    }

    /**
     * @covers       \Engelsystem\Http\Validation\Validates::numeric
     * @param mixed $value
     * @param bool  $result
     * @dataProvider provideNumeric
     */
    public function testNumeric($value, bool $result = true)
    {
        $val = new Validates;
        $this->assertTrue($val->numeric($value) === $result);
    }

    /**
     * @return array
     */
    public function provideRequired()
    {
        return [
            ['Lorem ipsum'],
            ['1234'],
            [1234],
            ['0'],
            [0],
            ['', false],
            [' ', false],
            [null, false],
        ];
    }

    /**
     * @covers       \Engelsystem\Http\Validation\Validates::required
     * @param mixed $value
     * @param bool  $result
     * @dataProvider provideRequired
     */
    public function testRequired($value, bool $result = true)
    {
        $val = new Validates;
        $this->assertTrue($val->required($value) === $result);
    }

    /**
     * @covers \Engelsystem\Http\Validation\Validates::getSize
     */
    public function testGetSize()
    {
        $val = new Validates;
        $this->assertTrue($val->max(42, [999]));
        $this->assertTrue($val->max('99', [100]));
        $this->assertFalse($val->max('101', [100]));
        $this->assertTrue($val->max('lorem', [5]));
        $this->assertFalse($val->max('Lorem Ipsum', [5]));
    }

    /**
     * @covers \Engelsystem\Http\Validation\Validates::validateParameterCount
     */
    public function testValidateParameterCount()
    {
        $val = new Validates;
        $this->assertTrue($val->between(42, [1, 100]));

        $this->expectException(InvalidArgumentException::class);
        $val->between(42, [1]);
    }
}
