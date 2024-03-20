<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTagsToFaq extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->create('tags', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name')->unique();
        });

        $this->schema->create('faq_tags', function (Blueprint $table): void {
            $table->increments('id');
            $this->references($table, 'faq');
            $this->references($table, 'tags');

            $table->unique(['faq_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->drop('faq_tags');
        $this->schema->drop('tags');
    }
}
