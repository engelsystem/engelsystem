<?php

use Phinx\Migration\AbstractMigration;

class Events extends AbstractMigration
{
     /**
     * Migrate Up.
     */
    public function up()
    {
        $events = $this->table('events', array('id' => 'event_id', 'limit' => 11));
        $events->addColumn('name', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('description', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('organizer', 'string', array('limit' => 255, 'null' => false))
                ->addColumn('start_date', 'datetime', array('null' => false, 'default' => '2000-01-01 00:00:00'))
                ->addColumn('end_date', 'datetime', array( 'null' => false, 'default' => '2000-01-01 00:00:00'))
                ->addIndex(array('name'), array('unique' => true, 'name' => 'Name'))
                ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
