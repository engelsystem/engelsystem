<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddShiftsDescription extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up()
    {
        $this->schema->table(
            'Shifts',
            function (Blueprint $table) {
                $table->text('description')->after('shifttype_id');
            }
        );
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $this->schema->table(
            'Shifts',
            function (Blueprint $table) {
                $table->dropColumn('description');
            }
        );
    }
}
