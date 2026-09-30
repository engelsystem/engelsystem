<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddReservedGoodieBy extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('users_state', function (Blueprint $table): void {
            $this->referencesUser($table, false, 'reserved_goodie_by')->nullable()->after('got_goodie_by');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('users_state', function (Blueprint $table): void {
            $table->dropForeign(['reserved_goodie_by']);
            $table->dropColumn('reserved_goodie_by');
        });
    }
}
