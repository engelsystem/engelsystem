<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIcalAlarmsToUsersSettings extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('users_settings', function (Blueprint $table): void {
            $table->boolean('ical_alarms')->default(false)->after('mobile_show');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('users_settings', function (Blueprint $table): void {
            $table->dropColumn('ical_alarms');
        });
    }
}
