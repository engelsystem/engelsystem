<?php

namespace Engelsystem\Test\Unit\Http;

use Engelsystem\Http\Exceptions\HttpRedirect;
use Engelsystem\Http\Exceptions\HttpTemporaryRedirect;
use PHPUnit\Framework\TestCase;

class HttpTemporaryRedirectTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Exceptions\HttpTemporaryRedirect::__construct
     */
    public function testConstruct()
    {
        $exception = new HttpTemporaryRedirect('https://lorem.ipsum/foo/bar');
        $this->assertInstanceOf(HttpRedirect::class, $exception);
        $this->assertEquals(302, $exception->getStatusCode());
    }
}
