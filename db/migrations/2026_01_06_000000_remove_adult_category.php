<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

/**
 * Remove the "Adult" minor category.
 *
 * Adults should be represented by minor_category_id = null, not by a separate category.
 * This migration:
 * 1. Sets minor_category_id to null for users with the "Adult" category
 * 2. Deletes the "Adult" category from minor_categories
 */
class RemoveAdultCategory extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $connection = $this->schema->getConnection();

        // Find the Adult category
        $adultCategory = $connection->table('minor_categories')
            ->where('name', 'Adult')
            ->first();

        if ($adultCategory === null) {
            // Adult category doesn't exist, nothing to do
            return;
        }

        // Update users with Adult category to have null minor_category_id
        $connection->table('users')
            ->where('minor_category_id', $adultCategory->id)
            ->update(['minor_category_id' => null]);

        // Delete the Adult category
        $connection->table('minor_categories')
            ->where('id', $adultCategory->id)
            ->delete();
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $connection = $this->schema->getConnection();

        // Check if Adult category already exists
        $exists = $connection->table('minor_categories')
            ->where('name', 'Adult')
            ->exists();

        if ($exists) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        // Recreate the Adult category
        $connection->table('minor_categories')->insert([
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
        ]);
    }
}
