<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class IncreaseSessionsTablePayloadSize extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        $this->schema->table('sessions', function (Blueprint $table) {
            $table->mediumText('payload')->change();
        });
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $this->schema->table('sessions', function (Blueprint $table) {
            $table->text('payload')->change();
        });
    }
}
