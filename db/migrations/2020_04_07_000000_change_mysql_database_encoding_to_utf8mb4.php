<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Query\Grammars\MySqlGrammar;

class ChangeMysqlDatabaseEncodingToUtf8mb4 extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $connection = $this->schema->getConnection();
        if (!$connection->getQueryGrammar() instanceof MySqlGrammar) {
            return;
        }

        $connection->unprepared('ALTER DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        foreach ($this->schema->getTables() as $table) {
            $connection->unprepared(
                'ALTER TABLE `' . $table['name'] . '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
            );
        }
    }

    // As utf8mb4 is a superset of utf8, there is nothing to do in the downgrade
}
