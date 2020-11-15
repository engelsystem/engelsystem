<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOauthTable extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->create('oauth', function (Blueprint $table) {
            $table->increments('id');
            $this->referencesUser($table);
            $table->string('provider');
            $table->string('identifier');
            $table->unique(['provider', 'identifier']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->drop('oauth');
    }
}
