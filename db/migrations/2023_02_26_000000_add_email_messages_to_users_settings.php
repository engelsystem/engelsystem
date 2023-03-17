<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddEmailMessagesToUsersSettings extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('users_settings', function (Blueprint $table): void {
            $table->boolean('email_messages')->default(false)->after('email_human');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('users_settings', function (Blueprint $table): void {
            $table->dropColumn('email_messages');
        });
    }
}
