<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class UsersSettingsAddEmailGoody extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $connection = $this->schema->getConnection();

        $this->schema->table(
            'users_settings',
            function (Blueprint $table): void {
                $table->boolean('email_goody')->default(false)->after('email_human');
            }
        );

        $connection
            ->table('users_settings')
            ->update(['email_goody' => $connection->raw('email_human')]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table(
            'users_settings',
            function (Blueprint $table): void {
                $table->dropColumn('email_goody');
            }
        );
    }
}
