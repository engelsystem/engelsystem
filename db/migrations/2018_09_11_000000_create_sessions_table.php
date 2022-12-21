<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->create('sessions', function (Blueprint $table): void {
            $table->string('id')->unique();
            $table->text('payload');
            $table->dateTime('last_activity')->useCurrent();
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->dropIfExists('sessions');
    }
}
