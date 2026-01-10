<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Exceptions;

use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Validation\Validator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(ValidationException::class, '__construct')]
#[CoversMethod(ValidationException::class, 'getValidator')]
class ValidationExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $validator = $this->createStub(Validator::class);

        $exception = new ValidationException($validator);

        $this->assertEquals($validator, $exception->getValidator());
    }
}
