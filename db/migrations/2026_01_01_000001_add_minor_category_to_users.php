<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddMinorCategoryToUsers extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('users', function (Blueprint $table): void {
            $this->references($table, 'minor_categories', 'minor_category_id')
                ->nullable()
                ->after('api_key');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('users', function (Blueprint $table): void {
            $table->dropForeign(['minor_category_id']);
            $table->dropColumn('minor_category_id');
        });
    }
}
