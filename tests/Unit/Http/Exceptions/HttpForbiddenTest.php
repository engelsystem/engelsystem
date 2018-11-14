<?php

namespace Engelsystem\Test\Unit\Http\Exceptions;

use Engelsystem\Http\Exceptions\HttpForbidden;
use PHPUnit\Framework\TestCase;

class HttpForbiddenTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Exceptions\HttpForbidden::__construct
     */
    public function testConstruct()
    {
        $exception = new HttpForbidden();
        $this->assertEquals(403, $exception->getStatusCode());
        $this->assertEquals('', $exception->getMessage());

        $exception = new HttpForbidden('Go away!');
        $this->assertEquals('Go away!', $exception->getMessage());
    }
}
