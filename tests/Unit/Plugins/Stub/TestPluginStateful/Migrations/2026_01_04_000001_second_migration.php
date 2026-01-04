<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Plugins\Stub\TestPluginStateful\Migrations;

use Engelsystem\Database\Migration\Migration;

class SecondMigration extends Migration
{
    public function up(): void
    {
        $this->schema->getConnection()
            ->table('event_config')
            ->insert([
                'name' => 'second_migration',
                'value' => '"Second Value"',
            ]);
    }

    public function down(): void
    {
        $this->schema->getConnection()
            ->table('event_config')
            ->where('name', 'second_migration')
            ->delete();
    }
}
