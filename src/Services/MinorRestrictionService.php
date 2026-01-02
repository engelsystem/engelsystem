<?php

declare(strict_types=1);

namespace Engelsystem\Services;

use Carbon\Carbon;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\MinorCategory;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;

/**
 * Service for handling minor volunteer restrictions.
 * Provides methods to check restrictions and validate shift signups.
 */
class MinorRestrictionService
{
    /**
     * Get the minor category for a user.
     */
    public function getCategory(User $user): ?MinorCategory
    {
        if (!$user->minor_category_id) {
            return null;
        }

        return $user->minorCategory;
    }

    /**
     * Check if a user is classified as a minor.
     */
    public function isMinor(User $user): bool
    {
        return $user->isMinor();
    }

    /**
     * Check if a user can work a specific angel type.
     */
    public function canWorkAngelType(User $user, AngelType $angelType): bool
    {
        $restrictions = $this->getRestrictions($user);
        $workCategory = $angelType->work_category ?? 'C';

        return $restrictions->allowsWorkCategory($workCategory);
    }

    /**
     * Get the restrictions for a user based on their minor category.
     */
    public function getRestrictions(User $user): MinorRestrictions
    {
        $category = $this->getCategory($user);

        if ($category === null) {
            return MinorRestrictions::forAdult();
        }

        return new MinorRestrictions(
            minShiftStartHour: $category->min_shift_start_hour,
            maxShiftEndHour: $category->max_shift_end_hour,
            maxHoursPerDay: $category->max_hours_per_day,
            allowedWorkCategories: $category->allowed_work_categories ?? [],
            canFillSlot: $category->can_fill_slot,
            requiresSupervisor: $category->requires_supervisor,
            canSelfSignup: $category->can_self_signup
        );
    }

    /**
     * Validate whether a user can work a specific shift.
     */
    public function canWorkShift(
        User $user,
        Shift $shift,
        ?AngelType $angelType = null
    ): ShiftValidationResult {
        if (!$this->isMinor($user)) {
            return ShiftValidationResult::success();
        }

        $restrictions = $this->getRestrictions($user);
        $errors = [];

        // Check consent approval
        if (!$user->hasConsentApproved()) {
            $errors[] = 'Consent must be verified at arrival before signing up for shifts';
        }

        // Check work category
        if ($angelType !== null) {
            $workCategory = $angelType->work_category ?? 'C';
            if (!$restrictions->allowsWorkCategory($workCategory)) {
                $errors[] = sprintf(
                    'Angel type "%s" (category %s) is not permitted. Allowed: %s',
                    $angelType->name,
                    $workCategory,
                    implode(', ', $restrictions->allowedWorkCategories)
                );
            }
        }

        // Check shift start time
        if ($restrictions->minShiftStartHour !== null) {
            $shiftStartHour = (int) $shift->start->format('H');
            if ($shiftStartHour < $restrictions->minShiftStartHour) {
                $errors[] = sprintf(
                    'Shift starts at %s, earliest permitted is %02d:00',
                    $shift->start->format('H:i'),
                    $restrictions->minShiftStartHour
                );
            }
        }

        // Check shift end time
        if ($restrictions->maxShiftEndHour !== null) {
            $shiftEndHour = (int) $shift->end->format('H');
            $shiftEndMinute = (int) $shift->end->format('i');
            $exceedsLimit = $shiftEndHour > $restrictions->maxShiftEndHour
                || ($shiftEndHour === $restrictions->maxShiftEndHour && $shiftEndMinute > 0);

            if ($exceedsLimit) {
                $errors[] = sprintf(
                    'Shift ends at %s, latest permitted is %02d:00',
                    $shift->end->format('H:i'),
                    $restrictions->maxShiftEndHour
                );
            }
        }

        // Check daily hours limit
        if ($restrictions->maxHoursPerDay !== null) {
            $remainingHours = $this->getDailyHoursRemaining($user, $shift->start);
            $shiftDuration = $this->getShiftDurationHours($shift);

            if ($shiftDuration > $remainingHours) {
                $usedHours = $restrictions->maxHoursPerDay - $remainingHours;
                $errors[] = sprintf(
                    'Shift is %.1f hours, only %.1f remaining (%.1f of %d used)',
                    $shiftDuration,
                    $remainingHours,
                    $usedHours,
                    $restrictions->maxHoursPerDay
                );
            }
        }

        // Check supervisor requirement
        if ($restrictions->requiresSupervisor && $shift->requires_supervisor_for_minors) {
            if (!$this->shiftHasWillingSupervisor($shift, $user)) {
                $errors[] = 'No willing supervisor is signed up for this shift';
            }
        }

        if (count($errors) > 0) {
            return ShiftValidationResult::failure($errors);
        }

        return ShiftValidationResult::success();
    }

    /**
     * Get the remaining hours a user can work on a given day.
     */
    public function getDailyHoursRemaining(User $user, Carbon $date): float
    {
        $restrictions = $this->getRestrictions($user);

        if ($restrictions->maxHoursPerDay === null) {
            return PHP_FLOAT_MAX;
        }

        $usedHours = $this->getDailyHoursUsed($user, $date);

        return (float) $restrictions->maxHoursPerDay - $usedHours;
    }

    /**
     * Get the hours a user has worked/signed up for on a given day.
     */
    public function getDailyHoursUsed(User $user, Carbon $date): float
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        $entries = ShiftEntry::where('user_id', $user->id)
            ->whereHas('shift', function ($query) use ($startOfDay, $endOfDay): void {
                $query->where('start', '>=', $startOfDay)
                    ->where('start', '<=', $endOfDay);
            })
            ->with('shift')
            ->get();

        $totalHours = 0.0;
        foreach ($entries as $entry) {
            $totalHours += $this->getShiftDurationHours($entry->shift);
        }

        return $totalHours;
    }

    /**
     * Get the duration of a shift in hours.
     */
    public function getShiftDurationHours(Shift $shift): float
    {
        return $shift->start->diffInMinutes($shift->end) / 60.0;
    }

    /**
     * Check if a shift has a willing supervisor signed up.
     */
    public function shiftHasWillingSupervisor(Shift $shift, ?User $minorUser = null): bool
    {
        $entries = $shift->shiftEntries()->with('user')->get();

        foreach ($entries as $entry) {
            $entryUser = $entry->user;

            if ($minorUser !== null && $entryUser->id === $minorUser->id) {
                continue;
            }

            if ($this->isMinor($entryUser)) {
                continue;
            }

            if ($minorUser !== null && $this->isGuardianOf($entryUser, $minorUser)) {
                return true;
            }

            if ($this->isWillingSupervisor($entryUser)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a user is a willing supervisor.
     */
    public function isWillingSupervisor(User $user): bool
    {
        if ($this->isMinor($user)) {
            return false;
        }

        $status = $user->supervisorStatus;

        return $status !== null && $status->willing_to_supervise;
    }

    /**
     * Check if a user is a guardian of a minor.
     */
    public function isGuardianOf(User $potentialGuardian, User $minor): bool
    {
        return $minor->guardians()
            ->where('guardian_user_id', $potentialGuardian->id)
            ->valid()
            ->exists();
    }

    /**
     * Get all available supervisors for a shift.
     *
     * @return User[]
     */
    public function getAvailableSupervisorsForShift(Shift $shift): array
    {
        $supervisors = [];
        $entries = $shift->shiftEntries()->with('user')->get();

        foreach ($entries as $entry) {
            $user = $entry->user;

            if (!$this->isMinor($user) && $this->isWillingSupervisor($user)) {
                $supervisors[] = $user;
            }
        }

        return $supervisors;
    }

    /**
     * Check if a user can self-signup or needs a guardian.
     */
    public function canSelfSignup(User $user): bool
    {
        return $this->getRestrictions($user)->canSelfSignup;
    }

    /**
     * Check if a user's participation should count toward shift quota.
     */
    public function countsTowardQuota(User $user): bool
    {
        return $this->getRestrictions($user)->canFillSlot;
    }
}
