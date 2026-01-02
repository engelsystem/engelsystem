<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMinorCategoriesTable extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->create('minor_categories', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('min_shift_start_hour')->nullable();
            $table->unsignedTinyInteger('max_shift_end_hour')->nullable();
            $table->unsignedTinyInteger('max_hours_per_day')->nullable();
            $table->json('allowed_work_categories');
            $table->boolean('can_fill_slot')->default(true);
            $table->boolean('requires_supervisor')->default(true);
            $table->boolean('can_self_signup')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->drop('minor_categories');
    }
}
