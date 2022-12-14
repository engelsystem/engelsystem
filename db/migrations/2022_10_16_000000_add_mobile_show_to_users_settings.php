<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddMobileShowToUsersSettings extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table(
            'users_settings',
            function (Blueprint $table): void {
                $table->boolean('mobile_show')->default(false)->after('email_news');
            }
        );
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table(
            'users_settings',
            function (Blueprint $table): void {
                $table->dropColumn('mobile_show');
            }
        );
    }
}
