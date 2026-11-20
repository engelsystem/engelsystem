<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class UsersStateEmailConfirmed extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $db = $this->schema->getConnection();

        $this->schema->table('users_state', function (Blueprint $table): void {
            $table->boolean('email_confirmed')->default(false)->after('user_info');
        });

        // pre-confirm the fist account (admin) email address
        $db->table('users_state')
            ->where('user_id', 1)
            ->update(['email_confirmed' => true]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('users_state', function (Blueprint $table): void {
            $table->dropColumn('email_confirmed');
        });
    }
}
