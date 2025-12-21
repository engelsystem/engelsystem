<?php

declare(strict_types=1);

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
        $config = new Config($this->getDbConfig());
        if (!$config->get('timezone')) {
            $config->set('timezone', 'UTC');
        }
        $this->app->instance('config', $config);

        $serviceProvider = new DatabaseServiceProvider($this->app);
        $serviceProvider->register();
        $this->assertTrue($this->app->has(Database::class));
    }
}
