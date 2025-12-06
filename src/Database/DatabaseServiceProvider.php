<?php

declare(strict_types=1);

namespace Engelsystem\Database;

use Carbon\Carbon;
use Engelsystem\Config\Config;
use Engelsystem\Container\ServiceProvider;
use Exception;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Database\Connection as DatabaseConnection;
use PDO;
use PDOException;
use Throwable;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /** @var Config $config */
        $config = $this->app->get('config');
        /** @var CapsuleManager $capsule */
        $capsule = $this->app->make(CapsuleManager::class);
        $now = Carbon::now($config->get('timezone'));

        $dbConfig = $config->get('database');
        $capsule->addConnection(array_merge([
            'driver'    => 'mysql',
            'host'      => '',
            'database'  => '',
            'username'  => '',
            'password'  => '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'timezone'  => $now->format('P'),
            'prefix'    => '',
        ], $dbConfig));

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        $capsule->getConnection()->useDefaultSchemaGrammar();

        $pdo = null;
        try {
            $pdo = $capsule->getConnection()->getPdo();
        } catch (PDOException $e) {
            $this->exitOnError($e);
        }

        $this->app->instance(PDO::class, $pdo);
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
     *
     * @throws Exception
     */
    protected function exitOnError(Throwable $exception): void
    {
        throw new Exception('Error: Unable to connect to database', 0, $exception);
    }
}
