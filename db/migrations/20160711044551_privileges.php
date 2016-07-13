<?php

use Phinx\Migration\AbstractMigration;

class Privileges extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        // inserting only one row
        $Rows = [
            [
              'id'   => 39,
              'name' => 'admin_settings',
              'desc' => 'Admin Settings'
            ],
            [
              'id'    => 40,
              'name'  => 'admin_export',
              'desc' => 'Import and Export user data'
            ]
        ];

    $this->insert('Privileges', $Rows);
        // inserting multiple rows
        $rows = [
            [
              'id'    => 218,
              'group_id'  => -4,
              'privilege_id' => 39

            ],
            [

               'id'    => 337,
              'group_id'  => -4,
              'privilege_id' => 40

            ]
        ];

      $this->insert('GroupPrivileges', $rows);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
