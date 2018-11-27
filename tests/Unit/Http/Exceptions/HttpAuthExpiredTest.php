<?php

namespace Engelsystem\Test\Unit\Http\Exceptions;

use Engelsystem\Http\Exceptions\HttpAuthExpired;
use PHPUnit\Framework\TestCase;

class HttpAuthExpiredTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Exceptions\HttpAuthExpired::__construct
     */
    public function testConstruct()
    {
        $exception = new HttpAuthExpired();
        $this->assertEquals(419, $exception->getStatusCode());
        $this->assertEquals('Authentication Expired', $exception->getMessage());

        $exception = new HttpAuthExpired('Oops!');
        $this->assertEquals('Oops!', $exception->getMessage());
    }
}
