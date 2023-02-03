<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Exceptions;

use Engelsystem\Http\Exceptions\HttpNotFound;
use PHPUnit\Framework\TestCase;

class HttpNotFoundTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Exceptions\HttpNotFound::__construct
     */
    public function testConstruct(): void
    {
        $exception = new HttpNotFound();
        $this->assertEquals(404, $exception->getStatusCode());
        $this->assertEquals('', $exception->getMessage());

        $exception = new HttpNotFound('Nothing to see here!');
        $this->assertEquals('Nothing to see here!', $exception->getMessage());
    }
}
