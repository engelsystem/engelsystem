<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\DesignController;
use Engelsystem\Http\Response;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(DesignController::class, '__construct')]
#[CoversMethod(DesignController::class, 'index')]
#[AllowMockObjectsWithoutExpectations]
class DesignControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRenderer();
        $this->stubTranslator();
    }

    public function testIndex(): void
    {
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
