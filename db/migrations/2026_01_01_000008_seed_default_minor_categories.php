<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class SeedDefaultMinorCategories extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $connection = $this->schema->getConnection();

        // Only seed if table is empty
        $count = $connection->table('minor_categories')->count();
        if ($count > 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        $connection->table('minor_categories')->insert([
            [
                'name'                    => 'Accompanying Child',
                'description'             => 'Under 13, no work. Must stay with guardian.',
                'min_shift_start_hour'    => null,
                'max_shift_end_hour'      => null,
                'max_hours_per_day'       => 0,
                'allowed_work_categories' => json_encode([]),
                'can_fill_slot'           => false,
                'requires_supervisor'     => true,
                'can_self_signup'         => false,
                'display_order'           => 1,
                'is_active'               => true,
                'created_at'              => $now,
                'updated_at'              => $now,
            ],
            [
                'name'                    => 'Junior Angel',
                'description'             => 'Ages 13-14, or 15-17 in school term. Max 2h/day, 8-18h, light work.',
                'min_shift_start_hour'    => 8,
                'max_shift_end_hour'      => 18,
                'max_hours_per_day'       => 2,
                'allowed_work_categories' => json_encode(['A']),
                'can_fill_slot'           => true,
                'requires_supervisor'     => true,
                'can_self_signup'         => true,
                'display_order'           => 2,
                'is_active'               => true,
                'created_at'              => $now,
                'updated_at'              => $now,
            ],
            [
                'name'                    => 'Teen Angel',
                'description'             => 'Ages 15-17, holidays. Max 8h/day, 6-20h.',
                'min_shift_start_hour'    => 6,
                'max_shift_end_hour'      => 20,
                'max_hours_per_day'       => 8,
                'allowed_work_categories' => json_encode(['A', 'B']),
                'can_fill_slot'           => true,
                'requires_supervisor'     => true,
                'can_self_signup'         => true,
                'display_order'           => 3,
                'is_active'               => true,
                'created_at'              => $now,
                'updated_at'              => $now,
            ],
            // Note: Adults are represented by minor_category_id = null, not by a category
        ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $connection = $this->schema->getConnection();

        $connection->table('minor_categories')
            ->whereIn('name', [
                'Accompanying Child',
                'Junior Angel',
                'Teen Angel',
            ])
            ->delete();
    }
}
