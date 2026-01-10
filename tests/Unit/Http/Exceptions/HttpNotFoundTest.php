<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Exceptions;

use Engelsystem\Http\Exceptions\HttpNotFound;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(HttpNotFound::class, '__construct')]
class HttpNotFoundTest extends TestCase
{
    public function testConstruct(): void
    {
        $exception = new HttpNotFound();
        $this->assertEquals(404, $exception->getStatusCode());
        $this->assertEquals('', $exception->getMessage());

        $exception = new HttpNotFound('Nothing to see here!');
        $this->assertEquals('Nothing to see here!', $exception->getMessage());
    }
}
