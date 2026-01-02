<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddQuotaFlagToShiftEntries extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('shift_entries', function (Blueprint $table): void {
            $table->boolean('counts_toward_quota')
                ->default(true)
                ->after('freeloaded_comment');
            $this->referencesUser($table, false, 'supervised_by_user_id')
                ->nullable()
                ->after('counts_toward_quota');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('shift_entries', function (Blueprint $table): void {
            $table->dropForeign(['supervised_by_user_id']);
            $table->dropColumn(['counts_toward_quota', 'supervised_by_user_id']);
        });
    }
}
