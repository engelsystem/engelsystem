<?php

namespace Engelsystem;

/**
 * BO Class that stores all parameters used to filter shifts for users.
 *
 * @author msquare
 */
class ShiftsFilter
{
    /**
     * Shift is completely full.
     */
    const FILLED_FILLED = 1;

    /**
     * Shift has some free slots.
     */
    const FILLED_FREE = 0;

    /**
     * Has the user "user shifts admin" privilege?
     *
     * @var boolean
     */
    private $userShiftsAdmin;

    /** @var int[] */
    private $filled;

    /** @var int[] */
    private $rooms;

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
     * @param int[] $rooms
     * @param int[] $types
     */
    public function __construct($user_shifts_admin = false, $rooms = [], $types = [])
    {
        $this->rooms = $rooms;
        $this->types = $types;

        $this->filled = [
            ShiftsFilter::FILLED_FREE
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
            'rooms'           => $this->rooms,
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
        $this->userShiftsAdmin = $data['userShiftsAdmin'];
        $this->filled = $data['filled'];
        $this->rooms = $data['rooms'];
        $this->types = $data['types'];
        $this->startTime = $data['startTime'];
        $this->endTime = $data['endTime'];
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
    public function getRooms()
    {
        if (count($this->rooms) == 0) {
            return [0];
        }
        return $this->rooms;
    }

    /**
     * @param int[] $rooms
     */
    public function setRooms($rooms)
    {
        $this->rooms = $rooms;
    }

    /**
     * @return bool
     */
    public function isUserShiftsAdmin()
    {
        return $this->userShiftsAdmin;
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
