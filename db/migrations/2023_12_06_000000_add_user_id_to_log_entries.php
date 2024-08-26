<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUserIdToLogEntries extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('log_entries', function (Blueprint $table): void {
            $table->unsignedInteger('user_id')->after('id')->nullable();
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('log_entries', function (Blueprint $table): void {
            $table->dropForeign('log_entries_user_id_foreign');
            $table->dropColumn('user_id');
        });
    }
}
