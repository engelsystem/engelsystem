<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class RenameShirtToGoodie extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('users_state', function (Blueprint $table): void {
            $table->renameColumn('got_shirt', 'got_goodie');
        });

        $db = $this->schema->getConnection();
        $db->table('privileges')->where('name', 'admin_active')->update([
            'description' => 'Mark angels as active and if they got a goodie.',
        ]);
        $db->table('privileges')->where('name', 'user.edit.shirt')->update([
            'name' => 'user.goodie.edit',
            'description' => 'Edit user goodies',
        ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('users_state', function (Blueprint $table): void {
            $table->renameColumn('got_goodie', 'got_shirt');
        });

        $db = $this->schema->getConnection();
        $db->table('privileges')->where('name', 'admin_active')->update([
            'description' => 'Mark angels as active and if they got a t-shirt.',
        ]);
        $db->table('privileges')->where('name', 'user.goodie.edit')->update([
            'name' => 'user.edit.shirt',
            'description' => 'Edit user shirts',
        ]);
    }
}
