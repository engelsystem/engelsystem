<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTagsToShifts extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->create('shift_tags', function (Blueprint $table): void {
            $table->increments('id');
            $this->references($table, 'shifts');
            $this->references($table, 'tags');

            $table->unique(['shift_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->drop('shift_tags');
    }
}
