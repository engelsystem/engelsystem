<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTimestampsToQuestions extends Migration
{
    use ChangesReferences;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('questions', function (Blueprint $table) {
            $table->timestamp('answered_at')->after('answerer_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('questions', function (Blueprint $table) {
            $table->dropColumn('answered_at');
            $table->dropTimestamps();
        });
    }
}
