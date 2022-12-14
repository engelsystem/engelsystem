<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\HomeController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Response;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class HomeControllerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Controllers\HomeController::__construct
     * @covers \Engelsystem\Controllers\HomeController::index
     */
    public function testIndex(): void
    {
        $config = new Config(['home_site' => '/foo']);
        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, new User());
        /** @var Redirector|MockObject $redirect */
        $redirect = $this->createMock(Redirector::class);
        $this->setExpects($redirect, 'to', ['/foo'], new Response());

        $controller = new HomeController($auth, $config, $redirect);
        $controller->index();
    }
}
