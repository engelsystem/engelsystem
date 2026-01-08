<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\MinorCategory;
use Engelsystem\Models\User\User;

/**
 * @covers \Engelsystem\Models\MinorCategory
 */
class MinorCategoryTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\MinorCategory::users
     */
    public function testUsers(): void
    {
        $category = MinorCategory::create([
            'name'                    => 'Test Category',
            'allowed_work_categories' => ['A', 'B'],
        ]);

        $this->assertCount(0, $category->users);

        User::factory(3)->create(['minor_category_id' => $category->id]);

        $category->refresh();
        $this->assertCount(3, $category->users);
    }

    /**
     * @return array<array{bool, array<string>, string}>
     */
    public function allowsWorkCategoryDataProvider(): array
    {
        return [
            [true, ['A'], 'A'],
            [true, ['A', 'B'], 'A'],
            [true, ['A', 'B'], 'B'],
            [true, ['A', 'B', 'C'], 'C'],
            [false, ['A'], 'B'],
            [false, ['A', 'B'], 'C'],
            [false, [], 'A'],
        ];
    }

    /**
     * @covers       \Engelsystem\Models\MinorCategory::allowsWorkCategory
     * @dataProvider allowsWorkCategoryDataProvider
     *
     * @param array<string> $allowedCategories
     */
    public function testAllowsWorkCategory(bool $expected, array $allowedCategories, string $category): void
    {
        $minorCategory = new MinorCategory([
            'name'                    => 'Test',
            'allowed_work_categories' => $allowedCategories,
        ]);

        $this->assertEquals($expected, $minorCategory->allowsWorkCategory($category));
    }

    /**
     * @covers \Engelsystem\Models\MinorCategory::scopeActive
     */
    public function testScopeActive(): void
    {
        // Clear any seeded data first
        MinorCategory::query()->delete();

        MinorCategory::create([
            'name'                    => 'Active 1',
            'allowed_work_categories' => ['A'],
            'is_active'               => true,
            'display_order'           => 1,
        ]);
        MinorCategory::create([
            'name'                    => 'Inactive',
            'allowed_work_categories' => ['A'],
            'is_active'               => false,
            'display_order'           => 2,
        ]);
        MinorCategory::create([
            'name'                    => 'Active 2',
            'allowed_work_categories' => ['A'],
            'is_active'               => true,
            'display_order'           => 3,
        ]);

        $activeCategories = MinorCategory::active()->get();
        $this->assertCount(2, $activeCategories);
        $this->assertEquals(['Active 1', 'Active 2'], $activeCategories->pluck('name')->toArray());
    }

    /**
     * @covers \Engelsystem\Models\MinorCategory::scopeMinorOnly
     */
    public function testScopeMinorOnly(): void
    {
        // Clear any seeded data first
        MinorCategory::query()->delete();

        MinorCategory::create([
            'name'                    => 'Junior Angels',
            'allowed_work_categories' => ['A'],
            'requires_supervisor'     => true,
            'display_order'           => 1,
        ]);
        MinorCategory::create([
            'name'                    => 'Teen Helpers',
            'allowed_work_categories' => ['A', 'B'],
            'requires_supervisor'     => true,
            'display_order'           => 2,
        ]);
        MinorCategory::create([
            'name'                    => 'Adult Category',
            'allowed_work_categories' => ['A', 'B', 'C'],
            'requires_supervisor'     => false,
            'display_order'           => 3,
        ]);

        $minorCategories = MinorCategory::minorOnly()->get();
        $this->assertCount(2, $minorCategories);
        $this->assertEquals(['Junior Angels', 'Teen Helpers'], $minorCategories->pluck('name')->toArray());
    }

    /**
     * @covers \Engelsystem\Models\MinorCategory::boot
     */
    public function testBoot(): void
    {
        // Clear any seeded data first
        MinorCategory::query()->delete();

        MinorCategory::create([
            'name'                    => 'Third',
            'allowed_work_categories' => ['A'],
            'display_order'           => 3,
        ]);
        MinorCategory::create([
            'name'                    => 'First',
            'allowed_work_categories' => ['A'],
            'display_order'           => 1,
        ]);
        MinorCategory::create([
            'name'                    => 'Second',
            'allowed_work_categories' => ['A'],
            'display_order'           => 2,
        ]);

        $categories = MinorCategory::all();
        $this->assertEquals(['First', 'Second', 'Third'], $categories->pluck('name')->toArray());
    }

    /**
     * @covers \Engelsystem\Models\MinorCategory
     */
    public function testAttributeCasts(): void
    {
        $category = MinorCategory::create([
            'name'                    => 'Test',
            'min_shift_start_hour'    => '8',
            'max_shift_end_hour'      => '18',
            'max_hours_per_day'       => '6',
            'allowed_work_categories' => ['A', 'B'],
            'can_fill_slot'           => '1',
            'requires_supervisor'     => '0',
            'can_self_signup'         => '1',
            'display_order'           => '5',
            'is_active'               => '1',
        ]);

        $category->refresh();

        $this->assertIsInt($category->min_shift_start_hour);
        $this->assertIsInt($category->max_shift_end_hour);
        $this->assertIsInt($category->max_hours_per_day);
        $this->assertIsArray($category->allowed_work_categories);
        $this->assertIsBool($category->can_fill_slot);
        $this->assertIsBool($category->requires_supervisor);
        $this->assertIsBool($category->can_self_signup);
        $this->assertIsInt($category->display_order);
        $this->assertIsBool($category->is_active);
    }

    /**
     * @covers \Engelsystem\Models\MinorCategory
     */
    public function testDefaultAttributes(): void
    {
        $category = new MinorCategory(['name' => 'Test', 'allowed_work_categories' => []]);

        $this->assertNull($category->description);
        $this->assertNull($category->min_shift_start_hour);
        $this->assertNull($category->max_shift_end_hour);
        $this->assertNull($category->max_hours_per_day);
        $this->assertTrue($category->can_fill_slot);
        $this->assertTrue($category->requires_supervisor);
        $this->assertTrue($category->can_self_signup);
        $this->assertEquals(0, $category->display_order);
        $this->assertTrue($category->is_active);
    }
}
