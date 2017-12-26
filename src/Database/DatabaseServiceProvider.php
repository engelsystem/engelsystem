<?php

namespace Engelsystem\Database;

use Engelsystem\Container\ServiceProvider;
use Exception;
use PDO;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $config = $this->app->get('config');
        Db::connect(
            'mysql:host=' . $config->get('database')['host']
            . ';dbname=' . $config->get('database')['db']
            . ';charset=utf8',
            $config->get('database')['user'],
            $config->get('database')['pw']
        ) || $this->exitOnError();

        Db::getPdo()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        Db::getPdo()->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    /**
     * @throws Exception
     */
    protected function exitOnError()
    {
        throw new Exception('Error: Unable to connect to database');
    }
}
