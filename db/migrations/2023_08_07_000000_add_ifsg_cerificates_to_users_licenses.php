<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIfsgCerificatesToUsersLicenses extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('users_licenses', function (Blueprint $table): void {
            $table->boolean('ifsg_certificate')->default(false)->after('drive_12t');
            $table->boolean('ifsg_certificate_full')->default(false)->after('ifsg_certificate');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('users_licenses', function (Blueprint $table): void {
            $table->dropColumn('ifsg_certificate');
            $table->dropColumn('ifsg_certificate_full');
        });
    }
}
