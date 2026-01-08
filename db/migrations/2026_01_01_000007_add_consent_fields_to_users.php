<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddConsentFieldsToUsers extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('users', function (Blueprint $table): void {
            $this->referencesUser($table, false, 'consent_approved_by_user_id')
                ->nullable()
                ->after('minor_category_id');
            $table->dateTime('consent_approved_at')
                ->nullable()
                ->after('consent_approved_by_user_id');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('users', function (Blueprint $table): void {
            $table->dropForeign(['consent_approved_by_user_id']);
            $table->dropColumn(['consent_approved_by_user_id', 'consent_approved_at']);
        });
    }
}
