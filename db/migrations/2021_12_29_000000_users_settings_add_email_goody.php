<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class UsersSettingsAddEmailGoody extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up()
    {
        $this->schema->table(
            'users_settings',
            function (Blueprint $table) {
                $table->boolean('email_goody')->default(false)->after('email_human');
            }
        );

        $connection = $this->schema->getConnection();
        $connection
            ->table('users_settings')
            ->update(['email_goody' => $connection->raw('email_human')]);
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $this->schema->table(
            'users_settings',
            function (Blueprint $table) {
                $table->dropColumn('email_goody');
            }
        );
    }
}
