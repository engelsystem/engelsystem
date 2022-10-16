<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddMobileShowToUsersSettings extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        $this->schema->table(
            'users_settings',
            function (Blueprint $table) {
                $table->boolean('mobile_show')->default(false)->after('email_news');
            }
        );
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $this->schema->table(
            'users_settings',
            function (Blueprint $table) {
                $table->dropColumn('mobile_show');
            }
        );
    }
}
