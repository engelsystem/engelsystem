<?php

namespace Engelsystem;

use Exception;

/**
 * Represents a single lane in a shifts calendar.
 */
class ShiftCalendarLane
{
    /** @var int */
    private $firstBlockStartTime;

    /** @var int */
    private $blockCount;

    /** @var string */
    private $header;

    /** @var array[] */
    private $shifts = [];

    /**
     * ShiftCalendarLane constructor.
     *
     * @param string $header
     * @param int    $firstBlockStartTime Unix timestamp
     * @param int    $blockCount
     */
    public function __construct($header, $firstBlockStartTime, $blockCount)
    {
        $this->header = $header;
        $this->firstBlockStartTime = $firstBlockStartTime;
        $this->blockCount = $blockCount;
    }

    /**
     * Adds a shift to the lane, but only if it fits.
     * Returns true on success.
     *
     * @param array $shift The shift to add
     * @throws Exception if the shift doesn't fit into the lane.
     */
    public function addShift($shift)
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
     * @param array $newShift
     * @return bool
     * @internal param array $shift The shift to fit into this lane
     */
    public function shiftFits($newShift)
    {
        foreach ($this->shifts as $laneShift) {
            if (!($newShift['start'] >= $laneShift['end'] || $newShift['end'] <= $laneShift['start'])) {
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
     * @return array[]
     */
    public function getShifts()
    {
        return $this->shifts;
    }
}
