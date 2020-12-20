<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class CreateQuestionsPermissions extends Migration
{
    /**
     * Run the migration
     */
    public function up()
    {
        if ($this->schema->hasTable('Privileges')) {
            $db = $this->schema->getConnection();
            $db->table('Privileges')->insert([
                ['name' => 'question.add', 'desc' => 'Ask questions'],
                ['name' => 'question.edit', 'desc' => 'Answer questions'],
            ]);

            $userGroup = -20;
            $shiftCoordinatorGroup = -40;
            $addId = $db->table('Privileges')->where('name', 'question.add')->first()->id;
            $editId = $db->table('Privileges')->where('name', 'question.edit')->first()->id;
            $db->table('GroupPrivileges')->insert([
                ['group_id' => $userGroup, 'privilege_id' => $addId],
                ['group_id' => $shiftCoordinatorGroup, 'privilege_id' => $editId],
            ]);

            $db->table('Privileges')
                ->whereIn('name', ['user_questions', 'admin_questions'])
                ->delete();
        }
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        if (!$this->schema->hasTable('Privileges')) {
            return;
        }

        $db = $this->schema->getConnection();
        $db->table('Privileges')
            ->whereIn('name', ['question.add', 'question.edit'])
            ->delete();

        $db->table('Privileges')->insert([
            ['name' => 'user_questions', 'desc' => 'Let users ask questions'],
            ['name' => 'admin_questions', 'desc' => 'Answer user\'s questions'],
        ]);
        $userGroup = -20;
        $shiftCoordinatorGroup = -40;
        $bureaucratGroup = -60;
        $userQuestionsId = $db->table('Privileges')->where('name', 'user_questions')->first()->id;
        $adminQuestionsId = $db->table('Privileges')->where('name', 'admin_questions')->first()->id;
        $db->table('GroupPrivileges')->insert([
            ['group_id' => $userGroup, 'privilege_id' => $userQuestionsId],
            ['group_id' => $shiftCoordinatorGroup, 'privilege_id' => $adminQuestionsId],
            ['group_id' => $bureaucratGroup, 'privilege_id' => $adminQuestionsId],
        ]);
    }
}
