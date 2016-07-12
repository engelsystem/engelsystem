<?php

use Phinx\Migration\AbstractMigration;

class Settings extends AbstractMigration
{
   /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('Settings');
        $table->addColumn('event_name', 'string', array('limit' => 255))
                ->addColumn('buildup_start_date', 'integer', array('limit' => 11))
                ->addColumn('event_start_date', 'integer', array('limit' => 11))
                ->addColumn('event_end_date', 'integer', array('limit' => 11))
                ->addColumn('teardown_end_date', 'integer', array( 'limit' => 11))
                ->addColumn('event_welcome_msg', 'string', array('limit' => 255))
                ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}