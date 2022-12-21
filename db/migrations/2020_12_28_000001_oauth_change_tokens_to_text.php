<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class OauthChangeTokensToText extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table(
            'oauth',
            function (Blueprint $table): void {
                $table->text('access_token')->change();
                $table->text('refresh_token')->change();
            }
        );
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table(
            'oauth',
            function (Blueprint $table): void {
                $table->string('access_token')->change();
                $table->string('refresh_token')->change();
            }
        );
    }
}
