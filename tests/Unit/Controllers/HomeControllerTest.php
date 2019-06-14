<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\HomeController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpTemporaryRedirect;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class HomeControllerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Controllers\HomeController::__construct
     * @covers \Engelsystem\Controllers\HomeController::index
     */
    public function testIndex()
    {
        $config = new Config(['home_site' => '/foo']);
        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, true);

        $controller = new HomeController($auth, $config);

        $this->expectException(HttpTemporaryRedirect::class);
        $controller->index();
    }
}
