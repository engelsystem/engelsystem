<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddHideRegisterToAngeltypes extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        if (!$this->schema->hasTable('AngelTypes')) {
            return;
        }

        $this->schema->table(
            'AngelTypes',
            function (Blueprint $table) {
                $table->boolean('hide_register')->default(false)->after('show_on_dashboard');
            }
        );
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        if (!$this->schema->hasTable('AngelTypes')) {
            return;
        }

        $this->schema->table(
            'AngelTypes',
            function (Blueprint $table) {
                $table->dropColumn('hide_register');
            }
        );
    }
}
