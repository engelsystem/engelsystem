<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class ImportUpdateSql extends Migration
{
    /**
     * Run the migration
     */
    public function up()
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
    public function down()
    {
        $this->schema->dropIfExists('UserWorkLog');
    }
}
