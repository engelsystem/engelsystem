<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\SessionHandlers;

use Engelsystem\Test\Unit\Http\SessionHandlers\Stub\ArrayHandler;
use PHPUnit\Framework\TestCase;

class AbstractHandlerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\SessionHandlers\AbstractHandler::open
     */
    public function testOpen(): void
    {
        $handler = new ArrayHandler();
        $return = $handler->open('/foo/bar', '1337asd098hkl7654');

        $this->assertTrue($return);
        $this->assertEquals('1337asd098hkl7654', $handler->getName());
        $this->assertEquals('/foo/bar', $handler->getSessionPath());
    }

    /**
     * @covers \Engelsystem\Http\SessionHandlers\AbstractHandler::close
     */
    public function testClose(): void
    {
        $handler = new ArrayHandler();
        $return = $handler->close();

        $this->assertTrue($return);
    }

    /**
     * @covers \Engelsystem\Http\SessionHandlers\AbstractHandler::gc
     */
    public function testGc(): void
    {
        $handler = new ArrayHandler();
        $return = $handler->gc(60 * 60 * 24);

        $this->assertEquals(0, $return);
    }
}
