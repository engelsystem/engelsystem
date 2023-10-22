<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class ImportInstallSql extends Migration
{
    /** @var array<string> */
    protected array $oldTables = [
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
        'UserWorkLog',
    ];

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->getConnection()->statement('SET FOREIGN_KEY_CHECKS=0;');

        // Delete all remaining tables
        foreach ($this->oldTables as $table) {
            if ($this->schema->hasTable($table)) {
                $this->schema->dropIfExists($table);
            }
        }
    }
}
