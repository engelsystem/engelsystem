<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class CleanupShortApiKeys extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $db = $this->schema->getConnection();
        $db->table('users')
            ->where($db->raw('LENGTH(api_key)'), '<=', 42)
            ->update(['api_key' => '']);
    }
}
