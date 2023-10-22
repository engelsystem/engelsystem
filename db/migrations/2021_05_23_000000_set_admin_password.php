<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Config\Config;
use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use stdClass;

class SetAdminPassword extends Migration
{
    use Reference;

    public function __construct(SchemaBuilder $schemaBuilder, protected Config $config)
    {
        parent::__construct($schemaBuilder);
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        $db = $this->schema->getConnection();
        /** @var stdClass $admin */
        $admin = $db->table('users')->where('name', 'admin')->first();
        $setupPassword = $this->config->get('setup_admin_password');

        if (
            !$admin
            || !password_verify('asdfasdf', $admin->password)
            || !$setupPassword
        ) {
            return;
        }

        $db->table('users')
            ->where('id', $admin->id)
            ->update(['password' => password_hash($setupPassword, PASSWORD_DEFAULT)]);
    }
}
