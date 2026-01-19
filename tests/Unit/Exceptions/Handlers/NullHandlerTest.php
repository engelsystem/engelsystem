<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Exceptions\Handlers;

use Engelsystem\Exceptions\Handlers\NullHandler;
use Engelsystem\Http\Request;
use ErrorException;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(NullHandler::class, 'render')]
class NullHandlerTest extends TestCase
{
    public function testRender(): void
    {
        $handler = new NullHandler();
        $request = new Request();
        $exception = new ErrorException();

        $this->expectOutputString('');
        $handler->render($request, $exception);
    }
}
