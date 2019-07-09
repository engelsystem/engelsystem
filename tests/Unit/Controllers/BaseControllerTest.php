<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Test\Unit\Controllers\Stub\ControllerImplementation;
use PHPUnit\Framework\TestCase;

class BaseControllerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Controllers\BaseController::getPermissions
     */
    public function testGetPermissions()
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
}
