<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Exceptions;

use Engelsystem\Http\Exceptions\HttpAuthExpired;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(HttpAuthExpired::class, '__construct')]
class HttpAuthExpiredTest extends TestCase
{
    public function testConstruct(): void
    {
        $exception = new HttpAuthExpired();
        $this->assertEquals(419, $exception->getStatusCode());
        $this->assertEquals('Authentication Expired', $exception->getMessage());

        $exception = new HttpAuthExpired('Oops!');
        $this->assertEquals('Oops!', $exception->getMessage());
    }
}
