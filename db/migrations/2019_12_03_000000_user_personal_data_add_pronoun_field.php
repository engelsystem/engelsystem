<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class UserPersonalDataAddPronounField extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('users_personal_data', function (Blueprint $table) {
            $table->string('pronoun', 15)
                ->nullable()
                ->default(null)
                ->after('last_name');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('users_personal_data', function (Blueprint $table) {
            $table->dropColumn('pronoun');
        });
    }
}
