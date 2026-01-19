<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\HomeController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Response;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(HomeController::class, '__construct')]
#[CoversMethod(HomeController::class, 'index')]
class HomeControllerTest extends TestCase
{
    public function testIndex(): void
    {
        $config = new Config(['home_site' => '/foo']);
        $auth = $this->createMock(Authenticator::class);
        $this->setExpects($auth, 'user', null, new User());
        $redirect = $this->createMock(Redirector::class);
        $this->setExpects($redirect, 'to', ['/foo'], new Response());

        $controller = new HomeController($auth, $config, $redirect);
        $controller->index();
    }
}
