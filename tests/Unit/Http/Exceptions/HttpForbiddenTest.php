<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Exceptions;

use Engelsystem\Http\Exceptions\HttpForbidden;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(HttpForbidden::class, '__construct')]
class HttpForbiddenTest extends TestCase
{
    public function testConstruct(): void
    {
        $exception = new HttpForbidden();
        $this->assertEquals(403, $exception->getStatusCode());
        $this->assertEquals('', $exception->getMessage());

        $exception = new HttpForbidden('Go away!');
        $this->assertEquals('Go away!', $exception->getMessage());
    }
}
