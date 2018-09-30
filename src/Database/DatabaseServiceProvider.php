<?php

namespace Engelsystem\Database;

use Engelsystem\Container\ServiceProvider;
use Exception;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Database\Connection as DatabaseConnection;
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
        $capsule->getConnection()->useDefaultSchemaGrammar();

        $pdo = null;
        try {
            $pdo = $capsule->getConnection()->getPdo();
        } catch (PDOException $e) {
            $this->exitOnError();
        }

        $this->app->instance(CapsuleManager::class, $capsule);
        $this->app->instance(Db::class, $capsule);
        Db::setDbManager($capsule);

        $connection = $capsule->getConnection();
        $this->app->instance(DatabaseConnection::class, $connection);

        $database = $this->app->make(Database::class);
        $this->app->instance(Database::class, $database);
        $this->app->instance('db', $database);
        $this->app->instance('db.pdo', $pdo);
        $this->app->instance('db.connection', $connection);
    }

    /**
     * @throws Exception
     */
    protected function exitOnError()
    {
        throw new Exception('Error: Unable to connect to database');
    }
}
