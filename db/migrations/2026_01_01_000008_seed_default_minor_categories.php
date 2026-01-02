<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\Facades\DB;

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
                'description'             => 'Under 13, no volunteer work permitted. Must stay with guardian at all times.',
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
                'description'             => 'Ages 13-14, or 15-17 during school term. Max 2 hours/day, 8:00-18:00, light work only.',
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
                'description'             => 'Ages 15-17, post-school or during holidays. Max 8 hours/day, 6:00-20:00, light and medium work.',
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
            [
                'name'                    => 'Adult',
                'description'             => 'Ages 18+. No restrictions on work hours or categories.',
                'min_shift_start_hour'    => null,
                'max_shift_end_hour'      => null,
                'max_hours_per_day'       => null,
                'allowed_work_categories' => json_encode(['A', 'B', 'C']),
                'can_fill_slot'           => true,
                'requires_supervisor'     => false,
                'can_self_signup'         => true,
                'display_order'           => 4,
                'is_active'               => true,
                'created_at'              => $now,
                'updated_at'              => $now,
            ],
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
                'Adult',
            ])
            ->delete();
    }
}
