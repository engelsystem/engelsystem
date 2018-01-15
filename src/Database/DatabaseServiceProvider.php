<?php

namespace Engelsystem\Database;

use Engelsystem\Container\ServiceProvider;
use Exception;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use PDOException;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $config = $this->app->get('config');
        $capsule = $this->app->make(CapsuleManager::class);

        $dbConfig = $config->get('database');
        $capsule->addConnection(array_merge([
            'driver'    => 'mysql',
            'host'      => '',
            'database'  => '',
            'username'  => '',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ], $dbConfig));

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        try {
            $capsule->getConnection()->getPdo();
        } catch (PDOException $e) {
            $this->exitOnError();
        }

        $this->app->instance('db', $capsule);
        Db::setDbManager($capsule);
    }

    /**
     * @throws Exception
     */
    protected function exitOnError()
    {
        throw new Exception('Error: Unable to connect to database');
    }
}
