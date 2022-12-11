<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class OauthAddTokens extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('oauth', function (Blueprint $table): void {
            $table->string('access_token')->nullable()->default(null)->after('identifier');
            $table->string('refresh_token')->nullable()->default(null)->after('access_token');
            $table->dateTime('expires_at')->nullable()->default(null)->after('refresh_token');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('oauth', function (Blueprint $table): void {
            $table->dropColumn('access_token');
            $table->dropColumn('refresh_token');
            $table->dropColumn('expires_at');
        });
    }
}
