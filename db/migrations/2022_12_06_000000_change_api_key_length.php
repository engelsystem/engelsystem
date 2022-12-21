<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeApiKeyLength extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('users', function (Blueprint $table): void {
            $table->string('api_key', 64)->change();
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('users', function (Blueprint $table): void {
            $table->string('api_key', 32)->change();
        });
    }
}
