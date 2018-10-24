<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class ImportInstallSql extends Migration
{
    protected $oldTables = [
        'AngelTypes',
        'EventConfig',
        'GroupPrivileges',
        'Groups',
        'LogEntries',
        'Messages',
        'NeededAngelTypes',
        'News',
        'NewsComments',
        'Privileges',
        'Questions',
        'Room',
        'ShiftEntry',
        'Shifts',
        'ShiftTypes',
        'User',
        'UserAngelTypes',
        'UserDriverLicenses',
        'UserGroups',
    ];

    /**
     * Run the migration
     */
    public function up()
    {
        foreach ($this->oldTables as $table) {
            if ($this->schema->hasTable($table)) {
                return;
            }
        }

        $sql = file_get_contents(__DIR__ . '/../install.sql');
        $this->schema->getConnection()->unprepared($sql);
    }


    /**
     * Reverse the migration
     */
    public
    function down()
    {
        $this->schema->getConnection()->statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($this->oldTables as $table) {
            if ($this->schema->hasTable($table)) {
                $this->schema->dropIfExists($table);
            }
        }
    }
}
