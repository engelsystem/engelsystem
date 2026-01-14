<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Validation;

use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Request;
use Engelsystem\Http\Validation\ValidatesRequest;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Test\Unit\Http\Validation\Stub\ValidatesRequestImplementation;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(ValidatesRequest::class, 'validate')]
#[CoversMethod(ValidatesRequest::class, 'setValidator')]
class ValidatesRequestTest extends TestCase
{
    public function testValidate(): void
    {
        $validator = $this->createMock(Validator::class);
        $matcher = $this->exactly(2);
        $validator->expects($matcher)
            ->method('validate')->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame(['foo' => 'bar'], $parameters[0]);
                    $this->assertSame(['foo' => 'required'], $parameters[1]);
                    return true;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame([], $parameters[0]);
                    $this->assertSame(['foo' => 'required'], $parameters[1]);
                    return false;
                }
            });
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
