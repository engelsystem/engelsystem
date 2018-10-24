<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class FixOldTables extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        $connection = $this->schema->getConnection();

        foreach (
            [
                'User'         => 'CreateDate',
                'NewsComments' => 'Datum',
            ] as $table => $column) {
            if (!$this->schema->hasTable($table)) {
                continue;
            }

            $connection
                ->table($table)
                ->where($column, '<', '0001-01-01 00:00:00')
                ->update([$column => '0001-01-01 00:00:00']);

            $this->schema->table($table, function (Blueprint $table) use ($column) {
                $table->dateTime($column)->default('0001-01-01 00:00:00')->change();
            });
        }
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
    }
}
