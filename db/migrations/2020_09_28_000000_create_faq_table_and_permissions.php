<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFaqTableAndPermissions extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        $this->schema->create('faq', function (Blueprint $table) {
            $table->increments('id');
            $table->string('question');
            $table->text('text');
            $table->timestamps();
        });

        if ($this->schema->hasTable('Privileges')) {
            $db = $this->schema->getConnection();
            $db->table('Privileges')->insert([
                ['name' => 'faq.view', 'desc' => 'View FAQ entries'],
                ['name' => 'faq.edit', 'desc' => 'Edit FAQ entries'],
            ]);

            $guestGroup = -10;
            $angelGroup = -20;
            $shiftCoordinatorGroup = -40;
            $viewId = $db->table('Privileges')->where('name', 'faq.view')->first()->id;
            $editId = $db->table('Privileges')->where('name', 'faq.edit')->first()->id;
            $db->table('GroupPrivileges')->insert([
                ['group_id' => $guestGroup, 'privilege_id' => $viewId],
                ['group_id' => $angelGroup, 'privilege_id' => $viewId],
                ['group_id' => $shiftCoordinatorGroup, 'privilege_id' => $editId],
            ]);

            $db->table('Privileges')
                ->whereIn('name', ['admin_faq'])
                ->delete();
        }
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $this->schema->drop('faq');

        if ($this->schema->hasTable('Privileges')) {
            $db = $this->schema->getConnection();
            $db->table('Privileges')
                ->whereIn('name', ['faq.view', 'faq.edit'])
                ->delete();

            $db->table('Privileges')->insert([
                ['name' => 'admin_faq', 'desc' => 'Edit FAQs'],
            ]);
            $bureaucratGroup = -60;
            $adminFaqId = $db->table('Privileges')->where('name', 'admin_faq')->first()->id;
            $db->table('GroupPrivileges')->insert([
                ['group_id' => $bureaucratGroup, 'privilege_id' => $adminFaqId],
            ]);
        }
    }
}
