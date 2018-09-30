<?php

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLogEntriesTable extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        $this->schema->create('log_entries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('level', 20);
            $table->text('message');
            $table->timestamp('created_at')->nullable();
        });

        if ($this->schema->hasTable('LogEntries')) {
            $this->schema->getConnection()->unprepared('
                INSERT INTO log_entries (`id`, `level`, `message`, `created_at`)
                SELECT `id`, `level`, `message`, FROM_UNIXTIME(`timestamp`) FROM LogEntries
            ');

            $this->schema->drop('LogEntries');
        }
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $this->schema->create('LogEntries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('level', 20);
            $table->text('message');
            $table->integer('timestamp');
        });

        $this->schema->getConnection()->unprepared('
            INSERT INTO LogEntries (`id`, `level`, `message`, `timestamp`)
            SELECT `id`, `level`, `message`, UNIX_TIMESTAMP(`created_at`) FROM log_entries
        ');

        $this->schema->dropIfExists('log_entries');
    }
}
