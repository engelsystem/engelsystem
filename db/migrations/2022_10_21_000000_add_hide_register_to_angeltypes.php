<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddHideRegisterToAngeltypes extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        if (!$this->schema->hasTable('AngelTypes')) {
            return;
        }

        $this->schema->table('AngelTypes', function (Blueprint $table): void {
            $table->boolean('hide_register')->default(false)->after('show_on_dashboard');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        if (!$this->schema->hasTable('AngelTypes')) {
            return;
        }

        $this->schema->table('AngelTypes', function (Blueprint $table): void {
            $table->dropColumn('hide_register');
        });
    }
}
