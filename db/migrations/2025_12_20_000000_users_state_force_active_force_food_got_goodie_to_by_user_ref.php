<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class UsersStateForceActiveForceFoodGotGoodieToByUserRef extends Migration
{
    use Reference;

    private function boolToUserId(string $bool_column, string $id_column, int|null $user_id): void
    {
        $connection = $this->schema->getConnection();
        $this->schema->table('users_state', function (Blueprint $table) use ($bool_column, $id_column): void {
            $this->referencesUser($table, false, $id_column)->nullable()->after($bool_column);
        });
        $connection
            ->table('users_state')
            ->where($bool_column, false)
            ->update([
                $id_column => null,
            ]);
        $connection
            ->table('users_state')
            ->where($bool_column, true)
            ->update([
                $id_column => $user_id,
            ]);
        $this->schema->table('users_state', function (Blueprint $table) use ($bool_column): void {
            $table->dropColumn($bool_column);
        });
    }

    private function userIdToBool(string $id_column, string $bool_column): void
    {
        $connection = $this->schema->getConnection();
        $this->schema->table('users_state', function (Blueprint $table) use ($id_column, $bool_column): void {
            $table->boolean($bool_column)->default(false)->after($id_column)->index();
        });
        $connection
            ->table('users_state')
            ->whereNull($id_column)
            ->update([
                $bool_column => false,
            ]);
        $connection
            ->table('users_state')
            ->whereNotNull($id_column)
            ->update([
                $bool_column => true,
            ]);
        $this->schema->table('users_state', function (Blueprint $table) use ($id_column): void {
            $table->dropForeign([$id_column]);
            $table->dropColumn($id_column);
        });
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        $user_id = $this->schema->getConnection()->table('users')->orderBy('id')->first()?->id;
        $this->boolToUserId('force_active', 'force_active_by', $user_id);
        $this->boolToUserId('force_food', 'force_food_by', $user_id);
        $this->boolToUserId('got_goodie', 'got_goodie_by', $user_id);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->userIdToBool('force_active_by', 'force_active');
        $this->userIdToBool('force_food_by', 'force_food');
        $this->userIdToBool('got_goodie_by', 'got_goodie');
    }
}
