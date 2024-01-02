<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Support\Str;

class CleanupShortApiKeys extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $db = $this->schema->getConnection();
        foreach ($db->table('users')->get() as $user) {
            if (Str::length($user->api_key) > 42) {
                continue;
            }

            $db->table('users')
                ->where('id', $user->id)
                ->update(['api_key' => bin2hex(random_bytes(32))]);
        }
    }
}
