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

        $initial_admin_pw_hash = getenv("INITIAL_ADMIN_PASSWORD_HASH");
        // Update initial admin password if env var exists
        if ($initial_admin_pw_hash !== false) {
            echo("Setting initial admin password.\n");
            // Admin password in the crypt(3) format
            $query = 'UPDATE `User` SET `Passwort` = ? WHERE `Nick` = "admin"';
            $this->schema->getConnection()->update($query, [$initial_admin_pw_hash]);
        }
    }


    /**
     * Reverse the migration
     */
    public function down()
    {
        $this->schema->getConnection()->statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($this->oldTables as $table) {
            if ($this->schema->hasTable($table)) {
                $this->schema->dropIfExists($table);
            }
        }
    }
}
