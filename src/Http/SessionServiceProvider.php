<?php

namespace Engelsystem\Http;

use Engelsystem\Container\ServiceProvider;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

class SessionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $sessionStorage = $this->getSessionStorage();
        $this->app->instance('session.storage', $sessionStorage);
        $this->app->bind(SessionStorageInterface::class, 'session.storage');

        $session = $this->app->make(Session::class);
        $this->app->instance('session', $session);

        /** @var Request $request */
        $request = $this->app->get('request');
        $request->setSession($session);

        $session->start();
    }

    /**
     * Returns the session storage
     *
     * @return SessionStorageInterface
     */
    protected function getSessionStorage()
    {
        if ($this->isCli()) {
            return $this->app->make(MockArraySessionStorage::class);
        }

        return $this->app->make(NativeSessionStorage::class, ['options' => ['cookie_httponly' => true]]);
    }

    /**
     * Test if is called from cli
     *
     * @return bool
     */
    protected function isCli()
    {
        return PHP_SAPI == 'cli';
    }
}
