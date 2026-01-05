<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Shifts;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;

/**
 * Tests for the Shift_free_entries function in includes/model/Shifts_model.php
 *
 * @covers ::Shift_free_entries
 */
class ShiftFreeEntriesTest extends TestCase
{
    use HasDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();

        // Include the Shifts_model.php file which contains Shift_free_entries
        require_once __DIR__ . '/../../../includes/model/Shifts_model.php';
    }

    /**
     * @covers ::Shift_free_entries
     */
    public function testFreeEntriesWithNormalEntries(): void
    {
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        $angelType->count = 5; // 5 slots needed

        /** @var Shift $shift */
        $shift = Shift::factory()->create();

        // Create 2 normal entries (counts_toward_quota = true by default)
        $entries = [];
        for ($i = 0; $i < 2; $i++) {
            /** @var User $user */
            $user = User::factory()->create();
            $entry = new ShiftEntry();
            $entry->shift()->associate($shift);
            $entry->angelType()->associate($angelType);
            $entry->user()->associate($user);
            $entry->save();
            $entries[] = $entry;
        }

        // Should have 3 free entries (5 - 2)
        $freeEntries = Shift_free_entries($angelType, $entries);
        $this->assertEquals(3, $freeEntries);
    }

    /**
     * @covers ::Shift_free_entries
     */
    public function testFreeEntriesExcludesNonCountingEntries(): void
    {
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        $angelType->count = 5; // 5 slots needed

        /** @var Shift $shift */
        $shift = Shift::factory()->create();

        // Create 2 normal entries
        $entries = [];
        for ($i = 0; $i < 2; $i++) {
            /** @var User $user */
            $user = User::factory()->create();
            $entry = new ShiftEntry();
            $entry->shift()->associate($shift);
            $entry->angelType()->associate($angelType);
            $entry->user()->associate($user);
            $entry->counts_toward_quota = true;
            $entry->save();
            $entries[] = $entry;
        }

        // Create 2 non-counting entries (accompanying children)
        for ($i = 0; $i < 2; $i++) {
            /** @var User $user */
            $user = User::factory()->create();
            $entry = new ShiftEntry();
            $entry->shift()->associate($shift);
            $entry->angelType()->associate($angelType);
            $entry->user()->associate($user);
            $entry->counts_toward_quota = false; // Does not count
            $entry->save();
            $entries[] = $entry;
        }

        // Should have 3 free entries (5 - 2), non-counting entries don't reduce
        $freeEntries = Shift_free_entries($angelType, $entries);
        $this->assertEquals(3, $freeEntries);
    }

    /**
     * @covers ::Shift_free_entries
     */
    public function testFreeEntriesExcludesFreelodedEntries(): void
    {
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        $angelType->count = 5; // 5 slots needed

        /** @var Shift $shift */
        $shift = Shift::factory()->create();

        /** @var User $freeloaderMarker */
        $freeloaderMarker = User::factory()->create();

        // Create 2 normal entries
        $entries = [];
        for ($i = 0; $i < 2; $i++) {
            /** @var User $user */
            $user = User::factory()->create();
            $entry = new ShiftEntry();
            $entry->shift()->associate($shift);
            $entry->angelType()->associate($angelType);
            $entry->user()->associate($user);
            $entry->save();
            $entries[] = $entry;
        }

        // Create 1 freeloaded entry
        /** @var User $user */
        $user = User::factory()->create();
        $entry = new ShiftEntry();
        $entry->shift()->associate($shift);
        $entry->angelType()->associate($angelType);
        $entry->user()->associate($user);
        $entry->freeloadedBy()->associate($freeloaderMarker);
        $entry->save();
        $entries[] = $entry;

        // Should have 3 free entries (5 - 2), freeloaded doesn't count
        $freeEntries = Shift_free_entries($angelType, $entries);
        $this->assertEquals(3, $freeEntries);
    }

    /**
     * @covers ::Shift_free_entries
     */
    public function testFreeEntriesWithMixedEntries(): void
    {
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        $angelType->count = 10; // 10 slots needed

        /** @var Shift $shift */
        $shift = Shift::factory()->create();

        /** @var User $freeloaderMarker */
        $freeloaderMarker = User::factory()->create();

        $entries = [];

        // 3 normal counting entries
        for ($i = 0; $i < 3; $i++) {
            /** @var User $user */
            $user = User::factory()->create();
            $entry = new ShiftEntry();
            $entry->shift()->associate($shift);
            $entry->angelType()->associate($angelType);
            $entry->user()->associate($user);
            $entry->counts_toward_quota = true;
            $entry->save();
            $entries[] = $entry;
        }

        // 2 non-counting entries (accompanying)
        for ($i = 0; $i < 2; $i++) {
            /** @var User $user */
            $user = User::factory()->create();
            $entry = new ShiftEntry();
            $entry->shift()->associate($shift);
            $entry->angelType()->associate($angelType);
            $entry->user()->associate($user);
            $entry->counts_toward_quota = false;
            $entry->save();
            $entries[] = $entry;
        }

        // 1 freeloaded entry
        /** @var User $user */
        $user = User::factory()->create();
        $entry = new ShiftEntry();
        $entry->shift()->associate($shift);
        $entry->angelType()->associate($angelType);
        $entry->user()->associate($user);
        $entry->freeloadedBy()->associate($freeloaderMarker);
        $entry->save();
        $entries[] = $entry;

        // Should have 7 free entries (10 - 3 counting entries)
        // Non-counting and freeloaded don't reduce the count
        $freeEntries = Shift_free_entries($angelType, $entries);
        $this->assertEquals(7, $freeEntries);
    }

    /**
     * @covers ::Shift_free_entries
     */
    public function testFreeEntriesWithNoEntries(): void
    {
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        $angelType->count = 5;

        $freeEntries = Shift_free_entries($angelType, []);
        $this->assertEquals(5, $freeEntries);
    }

    /**
     * @covers ::Shift_free_entries
     */
    public function testFreeEntriesDoesNotGoBelowZero(): void
    {
        /** @var AngelType $angelType */
        $angelType = AngelType::factory()->create();
        $angelType->count = 2; // Only 2 slots needed

        /** @var Shift $shift */
        $shift = Shift::factory()->create();

        // Create 5 entries (more than needed)
        $entries = [];
        for ($i = 0; $i < 5; $i++) {
            /** @var User $user */
            $user = User::factory()->create();
            $entry = new ShiftEntry();
            $entry->shift()->associate($shift);
            $entry->angelType()->associate($angelType);
            $entry->user()->associate($user);
            $entry->save();
            $entries[] = $entry;
        }

        // Should be 0, not negative
        $freeEntries = Shift_free_entries($angelType, $entries);
        $this->assertEquals(0, $freeEntries);
    }
}
