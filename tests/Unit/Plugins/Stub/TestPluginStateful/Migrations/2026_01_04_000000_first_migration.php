<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Plugins\Stub\TestPluginStateful\Migrations;

use Engelsystem\Database\Migration\Migration;

class FirstMigration extends Migration
{
    public function up(): void
    {
        $this->schema->getConnection()
            ->table('event_config')
            ->insert([
                'name' => 'first_migration',
                'value' => '"First Value"',
            ]);
    }

    public function down(): void
    {
        $this->schema->getConnection()
            ->table('event_config')
            ->where('name', 'first_migration')
            ->delete();
    }
}
