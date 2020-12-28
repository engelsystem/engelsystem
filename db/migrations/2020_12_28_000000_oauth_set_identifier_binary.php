<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Query\Grammars\MySqlGrammar;

class OauthSetIdentifierBinary extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up()
    {
        $connection = $this->schema->getConnection();
        if (!$connection->getQueryGrammar() instanceof MySqlGrammar) {
            return;
        }

        $connection->unprepared(
            '
            ALTER TABLE `oauth`
                CHANGE `identifier`
                    `identifier`
                    VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
                    NOT NULL
            '
        );
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $connection = $this->schema->getConnection();
        if (!$connection->getQueryGrammar() instanceof MySqlGrammar) {
            return;
        }

        $connection->unprepared(
            '
            ALTER TABLE `oauth`
                CHANGE `identifier`
                    `identifier`
                    VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
                    NOT NULL
            '
        );
    }
}
