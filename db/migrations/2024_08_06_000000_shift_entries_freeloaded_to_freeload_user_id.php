<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class ShiftEntriesFreeloadedToFreeloadUserId extends Migration
{
    private function boolToInt(): void
    {
        $connection = $this->schema->getConnection();
        $first_user = $connection->table('users')->min('id');
        $boolToInt = [
            false => null,
            true => $first_user,
        ];
        foreach ($boolToInt as $from => $to) {
            $connection
                ->table('shift_entries')
                ->where('freeloaded', $from)
                ->update([
                    'freeload_user_id'  => $to,
                ]);
        }
    }

    private function intToBool(): void
    {
        $connection = $this->schema->getConnection();
        $connection
            ->table('shift_entries')
            ->whereNull('freeload_user_id')
            ->update([
                'freeloaded'  => false,
            ]);
        $connection
            ->table('shift_entries')
            ->whereNotNull('freeload_user_id')
            ->update([
                'freeloaded'  => true,
            ]);
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('shift_entries', function (Blueprint $table): void {
            $table->integer('freeload_user_id')->nullable()->default(null)->after('freeloaded');
        });
        $this->boolToInt();
        $this->schema->table('shift_entries', function (Blueprint $table): void {
            $table->dropColumn('freeloaded');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('shift_entries', function (Blueprint $table): void {
            $table->boolean('freeloaded')->default(false)->after('freeload_user_id');
        });
        $this->intToBool();
        $this->schema->table('shift_entries', function (Blueprint $table): void {
            $table->dropColumn('freeload_user_id');
        });
    }
}
