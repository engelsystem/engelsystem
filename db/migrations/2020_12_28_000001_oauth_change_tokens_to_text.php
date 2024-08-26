<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class OauthChangeTokensToText extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('oauth', function (Blueprint $table): void {
            $table->text('access_token')->nullable()->change();
            $table->text('refresh_token')->nullable()->change();
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('oauth', function (Blueprint $table): void {
            $table->string('access_token')->nullable()->change();
            $table->string('refresh_token')->nullable()->change();
        });
    }
}
