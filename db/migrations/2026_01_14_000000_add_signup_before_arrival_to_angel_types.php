<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSignupBeforeArrivalToAngelTypes extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('angel_types', function (Blueprint $table): void {
            $table->boolean('shift_signup_before_arrival')->default(false)->after('restricted');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('angel_types', function (Blueprint $table): void {
            $table->dropColumn('shift_signup_before_arrival');
        });
    }
}
