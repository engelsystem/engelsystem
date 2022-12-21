<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLoremIpsumTable extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->create('lorem_ipsum', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('email');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->dropIfExists('lorem_ipsum');
    }
}
