<?php

use Phinx\Migration\AbstractMigration;

class User extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function up()
    {
        $table = $this->table('User');
        $table->addColumn('current_city', 'string', array('limit' => 255))
              ->addColumn('twitter', 'string', array('limit' => 255))
              ->addColumn('facebook', 'string', array('limit' => 255))
              ->addColumn('github', 'string', array('limit' => 255))
              ->addColumn('organization', 'string', array('limit' => 255))
              ->addColumn('organization_web', 'string', array('limit' => 255))
              ->addColumn('timezone', 'string', array('limit' => 255))
              ->addColumn('native_lang', 'string', array('limit' => 255))
              ->addColumn('other_langs', 'string', array('limit' => 255))
              ->addIndex(array('email'), array('unique' => true))
              ->save();
    }

    public function down()
    {

    }
}
