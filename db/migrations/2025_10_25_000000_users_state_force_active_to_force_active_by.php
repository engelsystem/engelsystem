<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class UsersStateForceActiveToForceActiveBy extends Migration
{
    use Reference;

    private function boolToUserId(): void
    {
        $connection = $this->schema->getConnection();
        $connection
            ->table('users_state')
            ->where('force_active', false)
            ->update([
                'force_active_by' => null,
            ]);
        $connection
            ->table('users_state')
            ->where('force_active', true)
            ->update([
                'force_active_by' => $connection->raw('user_id'),
            ]);
    }

    private function userIdToBool(): void
    {
        $connection = $this->schema->getConnection();
        $connection
            ->table('users_state')
            ->whereNull('force_active_by')
            ->update([
                'force_active' => false,
            ]);
        $connection
            ->table('users_state')
            ->whereNotNull('force_active_by')
            ->update([
                'force_active' => true,
            ]);
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('users_state', function (Blueprint $table): void {
            $this->referencesUser($table, false, 'force_active_by')->nullable()->after('force_active');
        });
        $this->boolToUserId();
        $this->schema->table('users_state', function (Blueprint $table): void {
            $table->dropColumn('force_active');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('users_state', function (Blueprint $table): void {
            $table->boolean('force_active')->default(false)->after('force_active_by')->index();
        });
        $this->userIdToBool();
        $this->schema->table('users_state', function (Blueprint $table): void {
            $table->dropForeign(['force_active_by']);
            $table->dropColumn('force_active_by');
        });
    }
}
