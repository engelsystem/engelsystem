<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class FixUserLanguages extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        $connection = $this->schema->getConnection();
        $connection
            ->table('users_settings')
            ->update([
                'language' => $connection->raw('REPLACE(language, ".UTF-8", "")')
            ]);
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $connection = $this->schema->getConnection();
        $connection
            ->table('users_settings')
            ->update([
                'language' => $connection->raw('CONCAT(language, ".UTF-8")')
            ]);
    }
}
