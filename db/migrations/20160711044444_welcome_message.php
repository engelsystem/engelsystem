<?php

use Phinx\Migration\AbstractMigration;

class WelcomeMessage extends AbstractMigration
{
     /**
     * Migrate Up.
     */
    public function up()
    {
        $singleRow = [
            'display_msg'  => "By completing this form you're registering as a Chaos-Angel. This script will create you an account in the angel task sheduler"
        ];
        $displaymsg = $this->table('Welcome_Message', array('id' => 'event_id'));
        $displaymsg->addColumn('display_msg', 'string', array('limit' => 255, 'null' => false))
                   ->insert($singleRow)
                   ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}