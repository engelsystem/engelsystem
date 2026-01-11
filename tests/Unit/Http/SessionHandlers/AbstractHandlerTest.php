<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\SessionHandlers;

use Engelsystem\Http\SessionHandlers\AbstractHandler;
use Engelsystem\Test\Unit\Http\SessionHandlers\Stub\ArrayHandler;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(AbstractHandler::class, 'open')]
#[CoversMethod(AbstractHandler::class, 'close')]
#[CoversMethod(AbstractHandler::class, 'gc')]
class AbstractHandlerTest extends TestCase
{
    public function testOpen(): void
    {
        $handler = new ArrayHandler();
        $return = $handler->open('/foo/bar', '1337asd098hkl7654');

        $this->assertTrue($return);
        $this->assertEquals('1337asd098hkl7654', $handler->getName());
        $this->assertEquals('/foo/bar', $handler->getSessionPath());
    }

    public function testClose(): void
    {
        $handler = new ArrayHandler();
        $return = $handler->close();

        $this->assertTrue($return);
    }

    public function testGc(): void
    {
        $handler = new ArrayHandler();
        $return = $handler->gc(60 * 60 * 24);

        $this->assertEquals(0, $return);
    }
}
