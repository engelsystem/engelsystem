<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Config\Config;
use Engelsystem\Database\Migration\Migration;
use Engelsystem\Helpers\Carbon;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use stdClass;

class CreateFirstUser extends Migration
{
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
        if ($db->table('users')->count() > 0) {
            return;
        }

        $db->table('users')->insert([
            'name' => 'admin',
            'email' => 'admin@localhost',
            'password' => password_hash('asdfasdf', PASSWORD_DEFAULT),
            'api_key' => bin2hex(random_bytes(16)),
            'created_at' => Carbon::now(),
        ]);

        /** @var stdClass $admin */
        $admin = $db->table('users')->where('name', 'admin')->first();
        foreach (['users_contact', 'users_personal_data', 'users_state'] as $table) {
            $db->table($table)->insert(['user_id' => $admin->id]);
        }
        $db->table('users_settings')->insert(['user_id' => $admin->id, 'language' => 'en_US', 'theme' => 1]);
    }
}
