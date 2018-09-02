<?php

namespace Engelsystem\Database\Migration;

use Engelsystem\Container\ServiceProvider;
use Engelsystem\Database\Db;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class MigrationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $schema = Db::connection()->getSchemaBuilder();
        $this->app->instance('db.scheme', $schema);
        $this->app->bind(SchemaBuilder::class, 'db.scheme');

        $migration = $this->app->make(Migrate::class);
        $this->app->instance('db.migration', $migration);
    }
}
