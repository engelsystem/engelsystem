<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateScheduleShiftTable extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up()
    {
        $this->schema->create(
            'schedules',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('url');
            }
        );

        $this->schema->create(
            'schedule_shift',
            function (Blueprint $table) {
                $table->integer('shift_id')->index()->unique();
                if ($this->schema->hasTable('Shifts')) {
                    // Legacy table access
                    $table->foreign('shift_id')
                        ->references('SID')->on('Shifts')
                        ->onUpdate('cascade')
                        ->onDelete('cascade');
                }

                $this->references($table, 'schedules');
                $table->uuid('guid');
            }
        );

        if ($this->schema->hasTable('Shifts')) {
            $this->schema->table(
                'Shifts',
                function (Blueprint $table) {
                    $table->dropColumn('PSID');
                }
            );
        }

        if ($this->schema->hasTable('Room')) {
            $this->schema->table(
                'Room',
                function (Blueprint $table) {
                    $table->dropColumn('from_frab');
                }
            );
        }
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        if ($this->schema->hasTable('Room')) {
            $this->schema->table(
                'Room',
                function (Blueprint $table) {
                    $table->boolean('from_frab')
                        ->default(false);
                }
            );
        }

        if ($this->schema->hasTable('Shifts')) {
            $this->schema->table(
                'Shifts',
                function (Blueprint $table) {
                    $table->integer('PSID')
                        ->nullable()->default(null)
                        ->unique();
                }
            );
        }

        $this->schema->drop('schedule_shift');
        $this->schema->drop('schedules');
    }
}
