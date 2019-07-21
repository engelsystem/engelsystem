<?php

namespace Engelsystem\Test\Unit\Http\Exceptions;

use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Validation\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidationExceptionTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Exceptions\ValidationException::__construct
     * @covers \Engelsystem\Http\Exceptions\ValidationException::getValidator
     */
    public function testConstruct()
    {
        /** @var Validator|MockObject $validator */
        $validator = $this->createMock(Validator::class);

        $exception = new ValidationException($validator);

        $this->assertEquals($validator, $exception->getValidator());
    }
}
