<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Exceptions;

use Engelsystem\Http\Exceptions\HttpRedirect;
use Engelsystem\Http\Exceptions\HttpTemporaryRedirect;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(HttpTemporaryRedirect::class, '__construct')]
class HttpTemporaryRedirectTest extends TestCase
{
    public function testConstruct(): void
    {
        $exception = new HttpTemporaryRedirect('https://lorem.ipsum/foo/bar');
        $this->assertInstanceOf(HttpRedirect::class, $exception);
        $this->assertEquals(302, $exception->getStatusCode());
    }
}
