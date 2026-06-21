<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserLanguagesTable extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->create('user_languages', function (Blueprint $table): void {
            $table->increments('id');
            $this->referencesUser($table);
            $table->string('language_code', 10);
            $table->boolean('is_native')->default(false);
            $table->unique(['user_id', 'language_code']);
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->drop('user_languages');
    }
}
