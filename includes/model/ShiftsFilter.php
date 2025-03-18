<?php

namespace Engelsystem;

use Engelsystem\Helpers\Carbon;

/**
 * BO Class that stores all parameters used to filter shifts for users.
 *
 * @author msquare
 */
class ShiftsFilter
{
    /**
     * Shift has some free slots.
     */
    public const FILLED_FREE = 0;

    /**
     * Shift is completely full.
     */
    public const FILLED_FILLED = 1;

    /**
     * Always include own shifts.
     */
    public const FILLED_OWN = 2;

    /**
     * Has the user "user shifts admin" privilege?
     *
     * @var boolean
     */
    private $userShiftsAdmin;

    /** @var int[] */
    private $filled;

    /** @var int[] */
    private $types;

    /** @var int unix timestamp */
    private $startTime = null;

    /** @var int unix timestamp */
    private $endTime = null;

    /**
     * ShiftsFilter constructor.
     *
     * @param bool  $user_shifts_admin
     * @param int[] $locations
     * @param int[] $angelTypes
     */
    public function __construct($user_shifts_admin = false, private $locations = [], $angelTypes = [])
    {
        $this->types = $angelTypes;

        $this->filled = [
            ShiftsFilter::FILLED_FREE,
        ];

        if ($user_shifts_admin) {
            $this->filled[] = ShiftsFilter::FILLED_FILLED;
        }
    }

    /**
     * @return array
     */
    public function sessionExport()
    {
        return [
            'userShiftsAdmin' => $this->userShiftsAdmin,
            'filled'          => $this->filled,
            'locations'       => $this->locations,
            'types'           => $this->types,
            'startTime'       => $this->startTime,
            'endTime'         => $this->endTime,
        ];
    }

    /**
     * @param array $data
     */
    public function sessionImport($data)
    {
        $this->userShiftsAdmin = $data['userShiftsAdmin'] ?? false;
        $this->filled = $data['filled'] ?? [];
        $this->locations = $data['locations'] ?? [];
        $this->types = $data['types'] ?? [];
        $this->startTime = $data['startTime'] ?? null;
        $this->endTime = $data['endTime'] ?? null;
    }

    /**
     * @return Carbon
     */
    public function getStart()
    {
        return Carbon::createFromTimestamp($this->startTime, Carbon::now()->timezone);
    }

    /**
     * @return int unix timestamp
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @param int $startTime unix timestamp
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * @return Carbon
     */
    public function getEnd()
    {
        return Carbon::createFromTimestamp($this->endTime, Carbon::now()->timezone);
    }

    /**
     * @return int unix timestamp
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @param int $endTime unix timestamp
     */
    public function setEndTime($endTime)
    {
        $filterMaxDuration = config('filter_max_duration') * 60 * 60;
        if ($filterMaxDuration && ($endTime - $this->startTime > $filterMaxDuration)) {
            $endTime = $this->startTime + $filterMaxDuration;
        }

        $this->endTime = $endTime;
    }

    /**
     * @return int[]
     */
    public function getTypes()
    {
        if (count($this->types) == 0) {
            return [0];
        }
        return $this->types;
    }

    /**
     * @param int[] $types
     */
    public function setTypes($types)
    {
        $this->types = $types;
    }

    /**
     * @return int[]
     */
    public function getLocations()
    {
        if (count($this->locations) == 0) {
            return [0];
        }
        return $this->locations;
    }

    /**
     * @param int[] $locations
     */
    public function setLocations($locations)
    {
        $this->locations = $locations;
    }

    /**
     * @param bool $userShiftsAdmin
     */
    public function setUserShiftsAdmin($userShiftsAdmin)
    {
        $this->userShiftsAdmin = $userShiftsAdmin;
    }

    /**
     * @return int[]
     */
    public function getFilled()
    {
        return $this->filled;
    }

    /**
     * @param int[] $filled
     */
    public function setFilled($filled)
    {
        $this->filled = $filled;
    }
}
