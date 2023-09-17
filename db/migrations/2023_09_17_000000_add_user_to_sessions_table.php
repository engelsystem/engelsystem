<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUserToSessionsTable extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('sessions', function (Blueprint $table): void {
            $this->referencesUser($table)->nullable()->index()->after('payload');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('sessions', function (Blueprint $table): void {
            $table->dropForeign('sessions_user_id_foreign');
            $table->dropColumn('user_id');
        });
    }
}
