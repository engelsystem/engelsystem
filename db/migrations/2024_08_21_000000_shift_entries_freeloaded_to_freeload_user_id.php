<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class ShiftEntriesFreeloadedToFreeloadUserId extends Migration
{
    use Reference;

    private function boolToUserId(): void
    {
        $connection = $this->schema->getConnection();
        $connection
            ->table('shift_entries')
            ->where('freeloaded', false)
            ->update([
                'freeloaded_by' => null,
            ]);
        $connection
            ->table('shift_entries')
            ->where('freeloaded', true)
            ->update([
                'freeloaded_by' => $connection->raw('user_id'),
            ]);
    }

    private function userIdToBool(): void
    {
        $connection = $this->schema->getConnection();
        $connection
            ->table('shift_entries')
            ->whereNull('freeloaded_by')
            ->update([
                'freeloaded' => false,
            ]);
        $connection
            ->table('shift_entries')
            ->whereNotNull('freeloaded_by')
            ->update([
                'freeloaded' => true,
            ]);
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('shift_entries', function (Blueprint $table): void {
            $this->referencesUser($table, false, 'freeloaded_by')->nullable()->after('freeloaded');
        });
        $this->boolToUserId();
        $this->schema->table('shift_entries', function (Blueprint $table): void {
            $table->dropIndex('shift_entries_freeloaded_index');
            $table->dropColumn('freeloaded');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('shift_entries', function (Blueprint $table): void {
            $table->boolean('freeloaded')->default(false)->after('freeloaded_by')->index();
        });
        $this->userIdToBool();
        $this->schema->table('shift_entries', function (Blueprint $table): void {
            $table->dropForeign(['freeloaded_by']);
            $table->dropColumn('freeloaded_by');
        });
    }
}
