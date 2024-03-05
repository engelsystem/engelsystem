<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIfsgConfirmedToUsersLicenses extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('users_licenses', function (Blueprint $table): void {
            $table->boolean('ifsg_confirmed')->default(false)->after('ifsg_certificate');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('users_licenses', function (Blueprint $table): void {
            $table->dropColumn('ifsg_confirmed');
        });
    }
}
