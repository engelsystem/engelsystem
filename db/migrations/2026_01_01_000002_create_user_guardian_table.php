<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserGuardianTable extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->create('user_guardian', function (Blueprint $table): void {
            $table->increments('id');
            $this->referencesUser($table, false, 'minor_user_id');
            $this->referencesUser($table, false, 'guardian_user_id');
            $table->boolean('is_primary')->default(false);
            $table->enum('relationship_type', ['parent', 'legal_guardian', 'delegated'])
                ->default('parent');
            $table->boolean('can_manage_account')->default(true);
            $table->dateTime('valid_from')->nullable();
            $table->dateTime('valid_until')->nullable();
            $table->timestamps();

            $table->index('minor_user_id');
            $table->index('guardian_user_id');
            $table->unique(['minor_user_id', 'guardian_user_id']);
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->drop('user_guardian');
    }
}
