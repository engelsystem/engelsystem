<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\NotificationType;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

abstract class ControllerTest extends TestCase
{
    use HasDatabase;

    protected Config $config;

    protected TestLogger $log;

    protected Response|MockObject $response;

    protected Request $request;

    protected Session $session;

    /**
     * @param string|string[] $value
     */
    protected function setNotification(string|array $value, NotificationType $type = NotificationType::MESSAGE): void
    {
        $this->session->set(
            'messages.' . $type->value,
            array_merge($this->session->get('messages.' . $type->value, []), (array) $value)
        );
    }

    protected function assertHasNotification(string $value, NotificationType $type = NotificationType::MESSAGE): void
    {
        $messages = $this->session->get('messages.' . $type->value, []);
        $this->assertTrue(in_array($value, $messages), 'Has ' . $type->value . ' notification: ' . $value);
    }

    protected function assertHasNoNotifications(?NotificationType $type = null): void
    {
        $messages = $this->session->get('messages' . ($type ? '.' . $type->value : ''), []);
        $this->assertEmpty($messages, 'Has no' . ($type ? ' ' . $type->value : '') . ' notification.');
    }

    /**
     * Setup environment
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();

        $this->request = Request::create('http://localhost');
        $this->app->instance('request', $this->request);
        $this->app->instance(Request::class, $this->request);
        $this->app->instance(ServerRequestInterface::class, $this->request);

        $this->response = $this->createMock(Response::class);
        $this->app->instance(Response::class, $this->response);

        $this->log = new TestLogger();
        $this->app->instance(LoggerInterface::class, $this->log);

        $this->session = new Session(new MockArraySessionStorage());
        $this->app->instance('session', $this->session);
        $this->app->instance(Session::class, $this->session);
        $this->app->instance(SessionInterface::class, $this->session);

        $this->app->bind(UrlGeneratorInterface::class, UrlGenerator::class);

        $this->config = new Config();
        $this->app->instance('config', $this->config);
        $this->app->instance(Config::class, $this->config);
    }
}
