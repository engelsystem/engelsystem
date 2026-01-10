<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\CreditsController;
use Engelsystem\Helpers\Version;
use Engelsystem\Http\Response;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(CreditsController::class, '__construct')]
#[CoversMethod(CreditsController::class, 'index')]
class CreditsControllerTest extends TestCase
{
    public function testIndex(): void
    {
        $response = $this->createMock(Response::class);
        $config = new Config(['foo' => 'bar', 'credits' => ['lor' => 'em']]);
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
