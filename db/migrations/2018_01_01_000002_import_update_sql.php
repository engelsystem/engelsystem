<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class ImportUpdateSql extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        if ($this->schema->hasTable('UserWorkLog')) {
            return;
        }

        $sql = file_get_contents(__DIR__ . '/../update.sql');
        $this->schema->getConnection()->unprepared($sql);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->dropIfExists('UserWorkLog');
    }
}
