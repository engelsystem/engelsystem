<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\SettingsController;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Response;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class SettingsControllerTest extends TestCase
{
    /**
     * @covers \Engelsystem\Controllers\SettingsController::__construct
     * @covers \Engelsystem\Controllers\SettingsController::oauth
     */
    public function testOauth()
    {
        $providers = ['foo' => ['lorem' => 'ipsum']];
        $config = new Config(['oauth' => $providers]);
        $session = new Session(new MockArraySessionStorage());
        $session->set('information', [['lorem' => 'ipsum']]);
        $this->app->instance('session', $session);
        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);
        $response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) use ($response, $providers) {
                $this->assertEquals('pages/settings/oauth.twig', $view);
                $this->assertArrayHasKey('information', $data);
                $this->assertArrayHasKey('providers', $data);
                $this->assertEquals($providers, $data['providers']);

                return $response;
            });

        $controller = new SettingsController($config, $response);
        $controller->oauth();
    }

    /**
     * @covers \Engelsystem\Controllers\SettingsController::oauth
     */
    public function testOauthNotConfigured()
    {
        $config = new Config(['oauth' => []]);
        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);

        $controller = new SettingsController($config, $response);

        $this->expectException(HttpNotFound::class);
        $controller->oauth();
    }
}
