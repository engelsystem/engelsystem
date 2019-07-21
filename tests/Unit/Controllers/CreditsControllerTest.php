<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\CreditsController;
use Engelsystem\Helpers\Version;
use Engelsystem\Http\Response;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class CreditsControllerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Controllers\CreditsController::__construct
     * @covers \Engelsystem\Controllers\CreditsController::index
     */
    public function testIndex()
    {
        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);
        $config = new Config(['foo' => 'bar', 'credits' => ['lor' => 'em']]);
        /** @var Version|MockObject $version */
        $version = $this->createMock(Version::class);

        $this->setExpects(
            $response,
            'withView',
            ['pages/credits.twig', ['credits' => ['lor' => 'em'], 'version' => '42.1.0-test']]
        );
        $this->setExpects($version, 'getVersion', [], '42.1.0-test');

        $controller = new CreditsController($response, $config, $version);
        $controller->index();
    }
}
