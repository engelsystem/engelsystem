<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * To allow for larger key names such as "2XL-G"
 */
class IncreaseTshirtFieldWidth extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('users_personal_data', function (Blueprint $table): void {
            $table->string('shirt_size', 10)->change();
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('users_personal_data', function (Blueprint $table): void {
            $table->string('shirt_size', 4)->change();
        });
    }
}
