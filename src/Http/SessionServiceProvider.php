<?php

namespace Engelsystem\Http;

use Engelsystem\Config\Config;
use Engelsystem\Container\ServiceProvider;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
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
        $this->app->instance(Session::class, $session);
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

        /** @var Config $config */
        $config = $this->app->get('config');
        $sessionConfig = $config->get('session');

        $handler = null;
        $driver = $sessionConfig['driver'];

        switch ($driver) {
            case 'pdo':
                $handler = $this->app->make(PdoSessionHandler::class, [
                    'pdoOrDsn' => $this->app->get('db.pdo'),
                    'options'  => [
                        'db_table'        => 'sessions',
                        'db_id_col'       => 'id',
                        'db_data_col'     => 'payload',
                        'db_lifetime_col' => 'lifetime',
                        'db_time_col'     => 'last_activity',
                    ],
                ]);
                break;
        }

        return $this->app->make(NativeSessionStorage::class, [
            'options' => [
                'cookie_httponly' => true,
                'name'            => $sessionConfig['name'],
            ],
            'handler' => $handler,
        ]);
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
