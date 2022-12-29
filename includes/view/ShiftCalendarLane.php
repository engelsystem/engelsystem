<?php

namespace Engelsystem;

use Engelsystem\Models\Shifts\Shift;
use Exception;

/**
 * Represents a single lane in a shifts calendar.
 */
class ShiftCalendarLane
{
    /** @var Shift[] */
    private $shifts = [];

    /**
     * ShiftCalendarLane constructor.
     *
     * @param string $header
     */
    public function __construct(private $header)
    {
    }

    /**
     * Adds a shift to the lane, but only if it fits.
     * Returns true on success.
     *
     * @param Shift $shift The shift to add
     * @throws Exception if the shift doesn't fit into the lane.
     */
    public function addShift(Shift $shift)
    {
        if ($this->shiftFits($shift)) {
            $this->shifts[] = $shift;
            return;
        }

        throw new Exception('Unable to add shift to shift calendar lane.');
    }

    /**
     * Returns true if given shift fits into this lane.
     *
     * @param Shift $newShift
     * @return bool
     * @internal param array $shift The shift to fit into this lane
     */
    public function shiftFits(Shift $newShift)
    {
        foreach ($this->shifts as $laneShift) {
            if (!($newShift->start >= $laneShift->end || $newShift->end <= $laneShift->start)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return Shift[]
     */
    public function getShifts()
    {
        return $this->shifts;
    }
}
