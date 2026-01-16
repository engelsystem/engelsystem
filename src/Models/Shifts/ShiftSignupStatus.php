<?php

declare(strict_types=1);

namespace Engelsystem\Models\Shifts;

enum ShiftSignupStatus: string
{
    /**
     * Shift has free places
     */
    case FREE = 'FREE';

    /**
     * Shift collides with users shifts
     */
    case COLLIDES = 'COLLIDES';

    /**
     * User cannot join because of a restricted angeltype or user is not in the angeltype
     */
    case ANGELTYPE = 'ANGELTYPE';

    /**
     * Shift is full
     */
    case OCCUPIED = 'OCCUPIED';

    /**
     * User is admin and can do what he wants.
     */
    case ADMIN = 'ADMIN';

    /**
     * Shift has already ended, no signup
     */
    case SHIFT_ENDED = 'SHIFT_ENDED';

    /**
     * Shift is not available yet
     */
    case NOT_YET = 'NOT_YET';

    /**
     * User is already signed up
     */
    case SIGNED_UP = 'SIGNED_UP';

    /**
     * User has to be arrived
     */
    case NOT_ARRIVED = 'NOT_ARRIVED';

    /**
     * Minor volunteer restrictions prevent signup (age-based work rules)
     */
    case MINOR_RESTRICTED = 'MINOR_RESTRICTED';
}
