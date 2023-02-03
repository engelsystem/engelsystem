<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Exceptions\Handlers;

use Engelsystem\Exceptions\Handlers\NullHandler;
use Engelsystem\Http\Request;
use ErrorException;
use PHPUnit\Framework\TestCase;

class NullHandlerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Exceptions\Handlers\NullHandler::render
     */
    public function testRender(): void
    {
        $handler = new NullHandler();
        $request = new Request();
        $exception = new ErrorException();

        $this->expectOutputString('');
        $handler->render($request, $exception);
    }
}
