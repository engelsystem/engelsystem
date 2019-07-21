<?php

namespace Engelsystem\Test\Unit\Http\Validation;

use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Request;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Test\Unit\Http\Validation\Stub\ValidatesRequestImplementation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatesRequestTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Validation\ValidatesRequest::validate
     * @covers \Engelsystem\Http\Validation\ValidatesRequest::setValidator
     */
    public function testValidate()
    {
        /** @var Validator|MockObject $validator */
        $validator = $this->createMock(Validator::class);
        $validator->expects($this->exactly(2))
            ->method('validate')
            ->withConsecutive(
                [['foo' => 'bar'], ['foo' => 'required']],
                [[], ['foo' => 'required']]
            )
            ->willReturnOnConsecutiveCalls(
                true,
                false
            );
        $validator->expects($this->once())
            ->method('getData')
            ->willReturn(['foo' => 'bar']);

        $implementation = new ValidatesRequestImplementation();
        $implementation->setValidator($validator);

        $return = $implementation->validateData(new Request([], ['foo' => 'bar']), ['foo' => 'required']);

        $this->assertEquals(['foo' => 'bar'], $return);

        $this->expectException(ValidationException::class);
        $implementation->validateData(new Request([], []), ['foo' => 'required']);
    }
}
