<?php

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        $this->schema->create('sessions', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->text('payload');
            $table->integer('last_activity');
            $table->integer('lifetime');
        });
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $this->schema->dropIfExists('sessions');
    }
}
