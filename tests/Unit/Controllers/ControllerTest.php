<?php

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
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
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

abstract class ControllerTest extends TestCase
{
    use HasDatabase;

    /** @var Config */
    protected $config;

    /** @var TestLogger */
    protected $log;

    /** @var Response|MockObject */
    protected $response;

    /** @var Request */
    protected $request;

    /** @var Session */
    protected $session;

    /**
     * @param string      $value
     * @param string|null $message
     */
    protected function assertHasNotification(string $value, string $message = null)
    {
        $messages = $this->session->get('messages', []);
        $this->assertTrue(in_array($value, $messages), $message ?: 'Session does not contain message "' . $value . '"');
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

        $this->app->bind(UrlGeneratorInterface::class, UrlGenerator::class);

        $this->config = new Config();
        $this->app->instance('config', $this->config);
        $this->app->instance(Config::class, $this->config);
    }
}
