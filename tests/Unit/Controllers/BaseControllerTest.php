<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Http\Request;
use Engelsystem\Test\Unit\Controllers\Stub\ControllerImplementation;
use PHPUnit\Framework\TestCase;

class BaseControllerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Controllers\BaseController::getPermissions
     */
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

    /**
     * @covers \Engelsystem\Controllers\BaseController::hasPermission
     */
    public function testHasPermission(): void
    {
        $request = new Request();
        $controller = new ControllerImplementation();

        $this->assertTrue($controller->hasPermission($request, 'yay'));
        $this->assertNull($controller->hasPermission($request, 'test'));
        $this->assertFalse($controller->hasPermission($request, 'nope'));
    }
}
