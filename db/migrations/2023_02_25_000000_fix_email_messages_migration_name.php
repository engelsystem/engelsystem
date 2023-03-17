<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class FixEmailMessagesMigrationName extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->getConnection()
            ->table('migrations')
            ->where('migration', '2023_02_26_000000_AddEmailMessagesToUsersSettings')
            ->update([
                'migration' => '2023_02_26_000000_add_email_messages_to_users_settings',
            ]);
    }

    // Down migration not needed when on same version
}
