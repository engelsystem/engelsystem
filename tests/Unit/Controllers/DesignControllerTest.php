<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\DesignController;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class DesignControllerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Controllers\DesignController::__construct
     * @covers \Engelsystem\Controllers\DesignController::index
     */
    public function testIndex()
    {
        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);
        $response->expects($this->once())
            ->method('withView')
            ->with('pages/design')
            ->willReturn($response);
        $request = new Request(['theme' => 42]);
        $config = new Config();

        $controller = new DesignController($response, $config);
        $return = $controller->index($request);

        $this->assertEquals($response, $return);
    }

    /**
     * @covers \Engelsystem\Controllers\DesignController::index
     */
    public function testIndexSetTheme()
    {
        $theme = ['name' => 'Meaning of Live'];
        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);
        $response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) use ($response, $theme) {
                $this->assertTrue(isset($data['theme']));
                $this->assertEquals($theme, $data['theme']);

                return $response;
            });
        $request = new Request();
        $request->attributes->set('theme', '42');
        $config = new Config(['themes' => [42 => $theme]]);

        $controller = new DesignController($response, $config);
        $controller->index($request);
    }
}
