<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class UserDriverLicenses extends AbstractMigration
{
     /**
     * Migrate Up.
     */
    public function up()
    {
        $usersdriverlicenses = $this->table('usersdriverlicenses', array('id' => 'user_id', 'limit' => 11));
        $usersdriverlicenses->addColumn('has_car', 'integer', array('limit' => MysqlAdapter::INT_TINY))
              ->addColumn('has_license_car', 'integer', array('limit' => MysqlAdapter::INT_TINY))
              ->addColumn('has_license_3_5t_transporter','integer', array('limit' => MysqlAdapter::INT_TINY))
              ->addColumn('has_license_7_5t_truck','integer', array('limit' => MysqlAdapter::INT_TINY))
              ->addColumn('has_license_12_5t_truck','integer', array('limit' => MysqlAdapter::INT_TINY))
              ->addColumn('has_license_forklift', 'integer', array('limit' => MysqlAdapter::INT_TINY))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
