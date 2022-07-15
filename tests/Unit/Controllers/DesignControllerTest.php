<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\DesignController;
use Engelsystem\Http\Response;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class DesignControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRenderer();
        $this->mockTranslator();
    }

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
            ->willReturnCallback(function (string $view, array $data) use ($response) {
                $this->assertTrue(isset($data['demo_user']));
                $this->assertTrue(isset($data['demo_user_2']));
                $this->assertIsArray($data['themes']);

                return $response;
            });
        $config = new Config(['themes' => [42 => ['name' => 'Foo']]]);

        $controller = new DesignController($response, $config);
        $return = $controller->index();

        $this->assertEquals($response, $return);
    }
}
