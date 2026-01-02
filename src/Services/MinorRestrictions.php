<?php

declare(strict_types=1);

namespace Engelsystem\Services;

/**
 * Value object representing minor restrictions for a user.
 * Null values indicate no restriction (adult user).
 *
 * @property-read int|null $minShiftStartHour Earliest shift start allowed (0-23), null = no restriction
 * @property-read int|null $maxShiftEndHour Latest shift end allowed (0-23), null = no restriction
 * @property-read int|null $maxHoursPerDay Maximum shift hours per day, null = no restriction
 * @property-read string[] $allowedWorkCategories Array of allowed work categories (e.g., ['A'], ['A','B'], or ['A','B','C'])
 * @property-read bool $canFillSlot If false, participation is always non-counting (accompanying)
 * @property-read bool $requiresSupervisor If true, minor must have a supervisor on shift
 * @property-read bool $canSelfSignup If false, only guardian can sign up minor
 */
readonly class MinorRestrictions
{
    /**
     * @param string[] $allowedWorkCategories
     */
    public function __construct(
        public ?int $minShiftStartHour,
        public ?int $maxShiftEndHour,
        public ?int $maxHoursPerDay,
        public array $allowedWorkCategories,
        public bool $canFillSlot,
        public bool $requiresSupervisor,
        public bool $canSelfSignup
    ) {
    }

    /**
     * Create restrictions for an adult user (no restrictions).
     */
    public static function forAdult(): self
    {
        return new self(
            minShiftStartHour: null,
            maxShiftEndHour: null,
            maxHoursPerDay: null,
            allowedWorkCategories: ['A', 'B', 'C'],
            canFillSlot: true,
            requiresSupervisor: false,
            canSelfSignup: true
        );
    }

    /**
     * Check if a work category is allowed.
     */
    public function allowsWorkCategory(string $category): bool
    {
        return in_array($category, $this->allowedWorkCategories, true);
    }

    /**
     * Check if there are any restrictions (i.e., this is a minor).
     */
    public function hasRestrictions(): bool
    {
        return $this->minShiftStartHour !== null
            || $this->maxShiftEndHour !== null
            || $this->maxHoursPerDay !== null
            || $this->allowedWorkCategories !== ['A', 'B', 'C']
            || !$this->canFillSlot
            || $this->requiresSupervisor
            || !$this->canSelfSignup;
    }
}
