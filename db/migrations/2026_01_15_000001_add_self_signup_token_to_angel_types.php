<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSelfSignupTokenToAngelTypes extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('angel_types', function (Blueprint $table): void {
            $table->string('self_signup_token', 255)->nullable()->after('restricted');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('angel_types', function (Blueprint $table): void {
            $table->dropColumn('self_signup_token');
        });
    }
}
