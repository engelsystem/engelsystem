<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;

class ChangeApiKeyLength extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('users', function (Blueprint $table): void {
            $table->string('api_key', 64)->change();
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $connection = $this->schema->getConnection();
        $data = $connection->table('users')->get(['id', 'api_key']);
        foreach ($data as $user) {
            if (Str::length($user->api_key) <= 32) {
                continue;
            }

            $key = Str::substr($user->api_key, 0, 32);
            $connection->table('users')
                ->where('id', $user->id)
                ->update(['api_key' => $key]);
        }

        $this->schema->table('users', function (Blueprint $table): void {
            $table->string('api_key', 32)->change();
        });
    }
}
