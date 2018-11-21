<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\Exceptions\HttpPermanentRedirect;
use Engelsystem\Http\Exceptions\HttpRedirect;
use PHPUnit\Framework\TestCase;

class HttpPermanentRedirectTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Exceptions\HttpPermanentRedirect::__construct
     */
    public function testConstruct()
    {
        $exception = new HttpPermanentRedirect('https://lorem.ipsum/foo/bar');
        $this->assertInstanceOf(HttpRedirect::class, $exception);
        $this->assertEquals(301, $exception->getStatusCode());
    }
}
