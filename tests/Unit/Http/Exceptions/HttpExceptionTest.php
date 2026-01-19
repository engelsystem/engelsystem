<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Exceptions;

use Engelsystem\Http\Exceptions\HttpException;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(HttpException::class, '__construct')]
#[CoversMethod(HttpException::class, 'getHeaders')]
#[CoversMethod(HttpException::class, 'getStatusCode')]
class HttpExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $exception = new HttpException(123);
        $this->assertEquals(123, $exception->getStatusCode());
        $this->assertEquals('', $exception->getMessage());
        $this->assertEquals([], $exception->getHeaders());

        $exception = new HttpException(404, 'Nothing found', ['page' => '/test']);
        $this->assertEquals('Nothing found', $exception->getMessage());
        $this->assertEquals(['page' => '/test'], $exception->getHeaders());
    }
}
