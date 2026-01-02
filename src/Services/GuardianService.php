<?php

declare(strict_types=1);

namespace Engelsystem\Services;

use Carbon\Carbon;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\MinorCategory;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserGuardian;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class GuardianService
{
    public function __construct(
        protected MinorRestrictionService $minorService
    ) {
    }

    /**
     * Link a guardian to a minor
     *
     * @param array{relationship_type?: string, is_primary?: bool, can_manage_account?: bool, valid_from?: Carbon|null, valid_until?: Carbon|null} $options
     */
    public function linkGuardianToMinor(User $guardian, User $minor, array $options = []): UserGuardian
    {
        $this->validateGuardianEligibility($guardian);

        if (!$minor->isMinor()) {
            throw new \InvalidArgumentException('Target user is not a minor');
        }

        if ($guardian->id === $minor->id) {
            throw new \InvalidArgumentException('Guardian and minor cannot be the same user');
        }

        $existingLink = UserGuardian::where('guardian_user_id', $guardian->id)
            ->where('minor_user_id', $minor->id)
            ->first();

        if ($existingLink) {
            throw new \InvalidArgumentException('Guardian is already linked to this minor');
        }

        $isPrimary = $options['is_primary'] ?? false;

        // If this is set as primary, or no other guardians exist, make it primary
        if ($isPrimary || $minor->guardians()->count() === 0) {
            $isPrimary = true;
            // Remove primary from other guardians
            if ($isPrimary) {
                UserGuardian::where('minor_user_id', $minor->id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }
        }

        return UserGuardian::create([
            'guardian_user_id'   => $guardian->id,
            'minor_user_id'      => $minor->id,
            'relationship_type'  => $options['relationship_type'] ?? 'parent',
            'is_primary'         => $isPrimary,
            'can_manage_account' => $options['can_manage_account'] ?? true,
            'valid_from'         => $options['valid_from'] ?? null,
            'valid_until'        => $options['valid_until'] ?? null,
        ]);
    }

    /**
     * Unlink a guardian from a minor
     *
     * @throws \InvalidArgumentException if trying to remove the only primary guardian
     */
    public function unlinkGuardianFromMinor(User $guardian, User $minor): void
    {
        $link = UserGuardian::where('guardian_user_id', $guardian->id)
            ->where('minor_user_id', $minor->id)
            ->first();

        if (!$link) {
            throw new \InvalidArgumentException('Guardian is not linked to this minor');
        }

        // Cannot remove the only primary guardian
        if ($link->is_primary) {
            $otherGuardians = UserGuardian::where('minor_user_id', $minor->id)
                ->where('guardian_user_id', '!=', $guardian->id)
                ->valid()
                ->count();

            if ($otherGuardians === 0) {
                throw new \InvalidArgumentException('Cannot remove the only guardian');
            }
        }

        $link->delete();
    }

    /**
     * Get all minors linked to a guardian
     *
     * @return Collection<int, User>
     */
    public function getLinkedMinors(User $guardian): Collection
    {
        return User::whereHas('guardians', function ($query) use ($guardian): void {
            $query->where('guardian_user_id', $guardian->id)
                ->valid();
        })->get();
    }

    /**
     * Get all guardians for a minor (only currently valid)
     *
     * @return Collection<int, User>
     */
    public function getGuardians(User $minor): Collection
    {
        return User::whereHas('minorsGuarded', function ($query) use ($minor): void {
            $query->where('minor_user_id', $minor->id)
                ->valid();
        })->get();
    }

    /**
     * Get the primary guardian for a minor
     */
    public function getPrimaryGuardian(User $minor): ?User
    {
        $link = UserGuardian::where('minor_user_id', $minor->id)
            ->where('is_primary', true)
            ->valid()
            ->first();

        return $link?->guardian;
    }

    /**
     * Set a guardian as the primary guardian for a minor
     */
    public function setPrimaryGuardian(User $guardian, User $minor): void
    {
        $link = UserGuardian::where('guardian_user_id', $guardian->id)
            ->where('minor_user_id', $minor->id)
            ->first();

        if (!$link) {
            throw new \InvalidArgumentException('Guardian is not linked to this minor');
        }

        // Remove primary from other guardians
        UserGuardian::where('minor_user_id', $minor->id)
            ->where('is_primary', true)
            ->update(['is_primary' => false]);

        $link->is_primary = true;
        $link->save();
    }

    /**
     * Check if a guardian can manage a specific minor
     */
    public function canManageMinor(User $guardian, User $minor): bool
    {
        $link = UserGuardian::where('guardian_user_id', $guardian->id)
            ->where('minor_user_id', $minor->id)
            ->valid()
            ->first();

        if (!$link) {
            return false;
        }

        return $link->can_manage_account;
    }

    /**
     * Get the guardian link details between a guardian and minor
     */
    public function getGuardianLink(User $guardian, User $minor): ?UserGuardian
    {
        return UserGuardian::where('guardian_user_id', $guardian->id)
            ->where('minor_user_id', $minor->id)
            ->first();
    }

    /**
     * Register a new minor account
     *
     * @param array{name: string, email?: string, password: string, minor_category_id: int, first_name?: string, last_name?: string, pronoun?: string} $data
     */
    public function registerMinor(User $guardian, array $data): User
    {
        $this->validateGuardianEligibility($guardian);

        $category = MinorCategory::find($data['minor_category_id']);
        if (!$category || !$category->is_active) {
            throw new \InvalidArgumentException('Invalid or inactive minor category');
        }

        $minor = new User([
            'name'              => $data['name'],
            'email'             => $data['email'] ?? $guardian->email,
            'password'          => $data['password'],
            'api_key'           => Str::random(32),
            'minor_category_id' => $data['minor_category_id'],
        ]);
        $minor->save();

        // Create associated models
        $minor->personalData()->create([
            'first_name' => $data['first_name'] ?? null,
            'last_name'  => $data['last_name'] ?? null,
            'pronoun'    => $data['pronoun'] ?? null,
        ]);
        $minor->contact()->create([]);
        $minor->settings()->create([
            'language' => 'en_US',
            'theme'    => 1,
        ]);
        $minor->state()->create([]);
        $minor->license()->create([]);

        // Link guardian as primary
        $this->linkGuardianToMinor($guardian, $minor, [
            'is_primary'        => true,
            'relationship_type' => 'parent',
        ]);

        return $minor->fresh();
    }

    /**
     * Update a minor's profile
     *
     * @param array{email?: string, first_name?: string, last_name?: string, pronoun?: string} $data
     */
    public function updateMinorProfile(User $guardian, User $minor, array $data): User
    {
        if (!$this->canManageMinor($guardian, $minor)) {
            throw new \InvalidArgumentException('Guardian cannot manage this minor');
        }

        if (isset($data['email'])) {
            $minor->email = $data['email'];
            $minor->save();
        }

        $personalData = $minor->personalData;
        if (isset($data['first_name'])) {
            $personalData->first_name = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $personalData->last_name = $data['last_name'];
        }
        if (isset($data['pronoun'])) {
            $personalData->pronoun = $data['pronoun'];
        }
        $personalData->save();

        return $minor->fresh();
    }

    /**
     * Change a minor's category
     */
    public function changeMinorCategory(User $guardian, User $minor, MinorCategory $category): void
    {
        if (!$this->canManageMinor($guardian, $minor)) {
            throw new \InvalidArgumentException('Guardian cannot manage this minor');
        }

        if (!$category->is_active) {
            throw new \InvalidArgumentException('Category is not active');
        }

        $minor->minor_category_id = $category->id;
        $minor->save();
    }

    /**
     * Generate a unique link code for a minor (for guardians to link)
     */
    public function generateMinorLinkCode(User $minor): string
    {
        if (!$minor->isMinor()) {
            throw new \InvalidArgumentException('User is not a minor');
        }

        // Generate a simple code based on user ID and a random component
        // In a production system, this would be stored and have expiration
        return strtoupper(substr(md5($minor->id . '-' . time() . '-' . Str::random(8)), 0, 8));
    }

    /**
     * Sign up a minor for a shift
     */
    public function signUpMinorForShift(
        User $guardian,
        User $minor,
        Shift $shift,
        AngelType $angelType
    ): ShiftEntry {
        if (!$this->canManageMinor($guardian, $minor)) {
            throw new \InvalidArgumentException('Guardian cannot manage this minor');
        }

        // Validate using MinorRestrictionService
        $result = $this->minorService->canWorkShift($minor, $shift, $angelType);
        if (!$result->isValid) {
            throw new \InvalidArgumentException($result->getErrorsAsString());
        }

        // Get minor's restrictions for quota determination
        $restrictions = $this->minorService->getRestrictions($minor);

        // Check if guardian is also on the shift (they can be supervisor)
        $guardianOnShift = ShiftEntry::where('shift_id', $shift->id)
            ->where('user_id', $guardian->id)
            ->exists();

        return ShiftEntry::create([
            'shift_id'            => $shift->id,
            'user_id'             => $minor->id,
            'angel_type_id'       => $angelType->id,
            'counts_toward_quota' => $restrictions->canFillSlot,
            'supervised_by_user_id' => $guardianOnShift ? $guardian->id : null,
        ]);
    }

    /**
     * Remove a minor from a shift
     */
    public function removeMinorFromShift(User $guardian, User $minor, ShiftEntry $entry): void
    {
        if (!$this->canManageMinor($guardian, $minor)) {
            throw new \InvalidArgumentException('Guardian cannot manage this minor');
        }

        if ($entry->user_id !== $minor->id) {
            throw new \InvalidArgumentException('Shift entry does not belong to this minor');
        }

        $entry->delete();
    }

    /**
     * Get a minor's shift history (past shifts)
     *
     * @return Collection<int, ShiftEntry>
     */
    public function getMinorShiftHistory(User $minor): Collection
    {
        return ShiftEntry::where('user_id', $minor->id)
            ->whereHas('shift', function ($query): void {
                $query->where('end', '<', Carbon::now());
            })
            ->with(['shift', 'angelType'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get a minor's upcoming shifts
     *
     * @return Collection<int, ShiftEntry>
     */
    public function getMinorUpcomingShifts(User $minor): Collection
    {
        return ShiftEntry::where('user_id', $minor->id)
            ->whereHas('shift', function ($query): void {
                $query->where('start', '>=', Carbon::now());
            })
            ->with(['shift', 'angelType'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Validate that a user is eligible to be a guardian
     *
     * @throws \InvalidArgumentException if user is not eligible
     */
    public function validateGuardianEligibility(User $guardian): void
    {
        if ($guardian->isMinor()) {
            throw new \InvalidArgumentException('Minors cannot be guardians');
        }
    }

    /**
     * Check if a user is a valid guardian (not a minor)
     */
    public function isEligibleGuardian(User $user): bool
    {
        return !$user->isMinor();
    }

    /**
     * Get guardian link with details for a specific minor
     *
     * @return Collection<int, UserGuardian>
     */
    public function getGuardianLinks(User $minor): Collection
    {
        return UserGuardian::where('minor_user_id', $minor->id)
            ->valid()
            ->with('guardian')
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Check if a specific guardian is the primary guardian for a minor
     */
    public function isPrimaryGuardian(User $guardian, User $minor): bool
    {
        $link = $this->getGuardianLink($guardian, $minor);
        return $link !== null && $link->is_primary;
    }

    /**
     * Add a secondary guardian to a minor (only primary guardian can do this)
     *
     * @param array{relationship_type?: string, can_manage_account?: bool, valid_from?: Carbon|null, valid_until?: Carbon|null} $options
     */
    public function addSecondaryGuardian(
        User $primaryGuardian,
        User $minor,
        User $newGuardian,
        array $options = []
    ): UserGuardian {
        if (!$this->isPrimaryGuardian($primaryGuardian, $minor)) {
            throw new \InvalidArgumentException('Only the primary guardian can add other guardians');
        }

        return $this->linkGuardianToMinor($newGuardian, $minor, array_merge($options, [
            'is_primary' => false,
        ]));
    }

    /**
     * Remove a secondary guardian (only primary guardian can do this)
     */
    public function removeSecondaryGuardian(User $primaryGuardian, User $minor, User $guardianToRemove): void
    {
        if (!$this->isPrimaryGuardian($primaryGuardian, $minor)) {
            throw new \InvalidArgumentException('Only the primary guardian can remove other guardians');
        }

        if ($primaryGuardian->id === $guardianToRemove->id) {
            throw new \InvalidArgumentException('Primary guardian cannot remove themselves');
        }

        $this->unlinkGuardianFromMinor($guardianToRemove, $minor);
    }
}
