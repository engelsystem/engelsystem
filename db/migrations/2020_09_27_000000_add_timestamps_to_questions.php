<?php

namespace Engelsystem\Migrations;

use Carbon\Carbon;
use Engelsystem\Database\Migration\Migration;
use Engelsystem\Models\Question;
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

        $now = Carbon::now();
        Question::query()->update([
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        Question::query()
            ->whereNotNull('answerer_id')
            ->update([
                'answered_at' => $now,
            ]);
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
