<?php

declare(strict_types=1);

namespace Demo\Plugin\Migrations;

use Engelsystem\Database\Migration\Migration;
use Engelsystem\Migrations\Reference;
use Illuminate\Database\Schema\Blueprint;

class DemoPluginInit extends Migration
{
    /**
     * Run up migration
     *
     * Creates needed data structures, should be as permissive as possible as other plugins might create
     * data structures too
     */
    public function up(): void
    {
        $this->schema->table('users_state', function (Blueprint $table): void {
            $table->string('demo_info')->nullable()->after('arrival_date');
        });
    }

    /**
     * Run down migration
     *
     * Should not remove data that might be referenced by the engelsystem or other plugins
     */
    public function down(): void
    {
        $this->schema->table('users_state', function (Blueprint $table): void {
            $table->dropColumn('demo_info');
        });
    }
}
