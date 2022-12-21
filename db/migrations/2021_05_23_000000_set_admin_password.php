<?php

namespace Engelsystem\Migrations;

use Engelsystem\Config\Config;
use Engelsystem\Database\Migration\Migration;
use Engelsystem\Helpers\Authenticator;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class SetAdminPassword extends Migration
{
    use Reference;

    public function __construct(SchemaBuilder $schemaBuilder, protected Authenticator $auth, protected Config $config)
    {
        parent::__construct($schemaBuilder);
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        $admin = $this->auth->authenticate('admin', 'asdfasdf');
        $setupPassword = $this->config->get('setup_admin_password');
        if (!$admin || !$setupPassword) {
            return;
        }

        $this->auth->setPassword($admin, $setupPassword);
    }
}
