<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPlugins extends Migration
{
    use Reference;

    protected int $developerId = 90;

    public function up(): void
    {
        $this->schema->create('plugins', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name')->unique();
            $table->boolean('enabled')->default(false);
            $table->string('version')->default('0.0.0');
        });

        $db = $this->schema->getConnection();
        $db->table('privileges')
            ->insert([
                'name' => 'plugin.edit',
                'description' => 'Manage plugins',
            ]);

        $editPlugin = $db->table('privileges')
            ->where('name', 'plugin.edit')
            ->get(['id'])
            ->first();

        $db->table('group_privileges')
            ->insertOrIgnore([
                ['group_id' => $this->developerId, 'privilege_id' => $editPlugin->id],
            ]);
    }

    public function down(): void
    {
        $this->schema->drop('plugins');

        $this->schema->getConnection()->table('privileges')
            ->where('name', 'plugin.edit')
            ->delete();
    }
}
