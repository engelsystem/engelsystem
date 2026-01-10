<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Controllers\BaseController;
use Engelsystem\Http\Request;
use Engelsystem\Test\Unit\Controllers\Stub\ControllerImplementation;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(BaseController::class, 'getPermissions')]
#[CoversMethod(BaseController::class, 'hasPermission')]
class BaseControllerTest extends TestCase
{
    public function testGetPermissions(): void
    {
        $controller = new ControllerImplementation();

        $this->assertEquals([
            'foo',
            'lorem' => [
                'ipsum',
                'dolor',
            ],
        ], $controller->getPermissions());

        $this->assertTrue(method_exists($controller, 'setValidator'));
    }

    public function testHasPermission(): void
    {
        $request = new Request();
        $controller = new ControllerImplementation();

        $this->assertTrue($controller->hasPermission($request, 'yay'));
        $this->assertNull($controller->hasPermission($request, 'test'));
        $this->assertFalse($controller->hasPermission($request, 'nope'));
    }
}
