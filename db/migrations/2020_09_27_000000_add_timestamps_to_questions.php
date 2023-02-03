<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Carbon\Carbon;
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
        $connection = $this->schema->getConnection();
        $now = Carbon::now();

        $this->schema->table('questions', function (Blueprint $table): void {
            $table->timestamp('answered_at')->after('answerer_id')->nullable();
            $table->timestamps();
        });

        $connection->table('questions')
            ->update([
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        $connection->table('questions')
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
        $this->schema->table('questions', function (Blueprint $table): void {
            $table->dropColumn('answered_at');
            $table->dropTimestamps();
        });
    }
}
