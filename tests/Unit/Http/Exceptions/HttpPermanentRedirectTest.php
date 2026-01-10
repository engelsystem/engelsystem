<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Exceptions;

use Engelsystem\Http\Exceptions\HttpPermanentRedirect;
use Engelsystem\Http\Exceptions\HttpRedirect;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(HttpPermanentRedirect::class, '__construct')]
class HttpPermanentRedirectTest extends TestCase
{
    public function testConstruct(): void
    {
        $exception = new HttpPermanentRedirect('https://lorem.ipsum/foo/bar');
        $this->assertInstanceOf(HttpRedirect::class, $exception);
        $this->assertEquals(301, $exception->getStatusCode());
    }
}
