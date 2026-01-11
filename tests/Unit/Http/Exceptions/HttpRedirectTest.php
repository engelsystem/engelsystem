<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Exceptions;

use Engelsystem\Http\Exceptions\HttpRedirect;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(HttpRedirect::class, '__construct')]
class HttpRedirectTest extends TestCase
{
    public function testConstruct(): void
    {
        $exception = new HttpRedirect('https://lorem.ipsum/foo/bar');
        $this->assertEquals(302, $exception->getStatusCode());
        $this->assertEquals('https://lorem.ipsum/foo/bar', $exception->getHeaders()['Location']);

        $exception = new HttpRedirect('/test', 301, ['lorem' => 'ipsum']);
        $this->assertEquals(301, $exception->getStatusCode());
        $this->assertEquals('ipsum', $exception->getHeaders()['lorem']);
    }
}
