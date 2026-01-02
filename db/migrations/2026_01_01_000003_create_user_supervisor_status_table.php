<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserSupervisorStatusTable extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->create('user_supervisor_status', function (Blueprint $table): void {
            $table->increments('id');
            $this->referencesUser($table);
            $table->boolean('willing_to_supervise')->default(false);
            $table->boolean('supervision_training_completed')->default(false);
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->drop('user_supervisor_status');
    }
}
