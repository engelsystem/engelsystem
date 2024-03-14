<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddDriveConfirmedToUsersLicenses extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('users_licenses', function (Blueprint $table): void {
            $table->boolean('drive_confirmed')->default(false)->after('drive_12t');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('users_licenses', function (Blueprint $table): void {
            $table->dropColumn('drive_confirmed');
        });
    }
}
