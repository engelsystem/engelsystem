<?php

namespace Engelsystem\Test\Feature\Database;

use Engelsystem\Config\Config;
use Engelsystem\Database\Database;
use Engelsystem\Database\DatabaseServiceProvider;

class DatabaseServiceProviderTest extends DatabaseTest
{
    /**
     * @covers \Engelsystem\Database\DatabaseServiceProvider::register()
     */
    public function testRegister(): void
    {
        $this->app->instance('config', new Config([
            'database' => $this->getDbConfig(),
            'timezone' => 'UTC',
        ]));

        $serviceProvider = new DatabaseServiceProvider($this->app);
        $serviceProvider->register();
        $this->assertTrue($this->app->has(Database::class));
    }
}
