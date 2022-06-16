<?php

namespace Engelsystem;

use Engelsystem\Models\Room;

class ShiftCalendarRenderer
{
    /**
     * 15m * 60s/m = 900s
     */
    const SECONDS_PER_ROW = 900;

    /**
     * Height of a block in pixel.
     * Do not change - corresponds with theme/css
     */
    const BLOCK_HEIGHT = 30;

    /**
     * Distance between two shifts in pixels
     */
    const MARGIN = 5;

    /**
     * Seconds added to the start and end time
     */
    const TIME_MARGIN = 1800;

    /** @var array */
    private $lanes;

    /** @var ShiftsFilter */
    private $shiftsFilter;

    /** @var int */
    private $firstBlockStartTime;

    /** @var int */
    private $lastBlockEndTime;

    /** @var int */
    private $blocksPerSlot = null;

    /** @var array[] */
    private $needed_angeltypes;

    /** @var array[] */
    private $shift_entries;

    /**
     * ShiftCalendarRenderer constructor.
     *
     * @param array[]      $shifts
     * @param array[]      $needed_angeltypes
     * @param array[]      $shift_entries
     * @param ShiftsFilter $shiftsFilter
     */
    public function __construct($shifts, $needed_angeltypes, $shift_entries, ShiftsFilter $shiftsFilter)
    {
        $this->shiftsFilter = $shiftsFilter;
        $this->firstBlockStartTime = $this->calcFirstBlockStartTime($shifts);
        $this->lastBlockEndTime = $this->calcLastBlockEndTime($shifts);
        $this->lanes = $this->assignShiftsToLanes($shifts);
        $this->needed_angeltypes = $needed_angeltypes;
        $this->shift_entries = $shift_entries;
    }

    /**
     * Assigns the shifts to different lanes per room if they collide
     *
     * @param array[] $shifts The shifts to assign
     * @return array Returns an array that assigns a room_id to an array of ShiftCalendarLane containing the shifts
     */
    private function assignShiftsToLanes($shifts)
    {
        // array that assigns a room id to a list of lanes (per room)
        $lanes = [];

        foreach ($shifts as $shift) {
            $room_id = $shift['RID'];
            $room = new Room();
            $room->name = $shift['room_name'];
            $room->setAttribute('id', $room_id);
            $header = Room_name_render($room);
            if (!isset($lanes[$room_id])) {
                // initialize room with one lane
                $lanes[$room_id] = [
                    new ShiftCalendarLane($header, $this->getFirstBlockStartTime(), $this->getBlocksPerSlot())
                ];
            }
            // Try to add the shift to the existing lanes for this room
            $shift_added = false;
            foreach ($lanes[$room_id] as $lane) {
                /** @var ShiftCalendarLane $lane */
                if ($lane->shiftFits($shift)) {
                    $lane->addShift($shift);
                    $shift_added = true;
                    break;
                }
            }
            // If all lanes for this room are busy, create a new lane and add shift to it
            if (!$shift_added) {
                $newLane = new ShiftCalendarLane($header, $this->getFirstBlockStartTime(), $this->getBlocksPerSlot());
                $newLane->addShift($shift);
                $lanes[$room_id][] = $newLane;
            }
        }

        return $lanes;
    }

    /**
     * @return int
     */
    public function getFirstBlockStartTime()
    {
        return $this->firstBlockStartTime;
    }

    /**
     * @return int
     */
    public function getLastBlockEndTime()
    {
        return $this->lastBlockEndTime;
    }

    /**
     * @return float
     */
    public function getBlocksPerSlot()
    {
        if (is_null($this->blocksPerSlot)) {
            $this->blocksPerSlot = $this->calcBlocksPerSlot();
        }
        return $this->blocksPerSlot;
    }

    /**
     * Renders the whole calendar
     *
     * @return string the generated html
     */
    public function render()
    {
        if (count($this->lanes) == 0) {
            return info(__('No shifts found.'), true);
        }

        return div('shift-calendar table-responsive', [
                $this->renderTimeLane(),
                $this->renderShiftLanes()
            ]) . $this->renderLegend();
    }

    /**
     * Renders the lanes containing the shifts
     *
     * @return string
     */
    private function renderShiftLanes()
    {
        $html = '';
        foreach ($this->lanes as $room_lanes) {
            foreach ($room_lanes as $lane) {
                $html .= $this->renderLane($lane);
            }
        }

        return $html;
    }

    /**
     * Renders a single lane
     *
     * @param ShiftCalendarLane $lane The lane to render
     * @return string
     */
    private function renderLane(ShiftCalendarLane $lane)
    {
        $shift_renderer = new ShiftCalendarShiftRenderer();
        $html = '';
        $rendered_until = $this->getFirstBlockStartTime();

        foreach ($lane->getShifts() as $shift) {
            while ($rendered_until + ShiftCalendarRenderer::SECONDS_PER_ROW <= $shift['start']) {
                $html .= $this->renderTick($rendered_until);
                $rendered_until += ShiftCalendarRenderer::SECONDS_PER_ROW;
            }

            list ($shift_height, $shift_html) = $shift_renderer->render(
                $shift,
                $this->needed_angeltypes[$shift['SID']],
                $this->shift_entries[$shift['SID']],
                auth()->user()
            );
            $html .= $shift_html;
            $rendered_until += $shift_height * ShiftCalendarRenderer::SECONDS_PER_ROW;
        }

        while ($rendered_until < $this->getLastBlockEndTime()) {
            $html .= $this->renderTick($rendered_until);
            $rendered_until += ShiftCalendarRenderer::SECONDS_PER_ROW;
        }

        $bg = 'bg-' . theme_type();

        return div('lane', [
            div('header ' . $bg, $lane->getHeader()),
            $html
        ]);
    }

    /**
     * Renders a tick/block for given time
     *
     * @param int     $time  unix timestamp
     * @param boolean $label Should time labels be generated?
     * @return string rendered tick html
     */
    private function renderTick($time, $label = false)
    {
        if ($time % (24 * 60 * 60) == 23 * 60 * 60) {
            if (!$label) {
                return div('tick day');
            }
            return div('tick day', [
                date(__('m-d'), $time) .'<br>'.date(__('H:i'), $time)
            ]);
        } elseif ($time % (60 * 60) == 0) {
            if (!$label) {
                return div('tick hour');
            }
            return div('tick hour', [
                date(__('m-d'), $time) .'<br>'.date(__('H:i'), $time)
            ]);
        }
        return div('tick');
    }

    /**
     * Renders the left time lane including hour/day ticks
     *
     * @return string
     */
    private function renderTimeLane()
    {
        $bg = '';
        if (theme_type() === 'light') {
            $bg = 'bg-light';
        }

        $time_slot = [
            div('header ' . $bg, [
                __('Time')
            ])
        ];
        for ($block = 0; $block < $this->getBlocksPerSlot(); $block++) {
            $thistime = $this->getFirstBlockStartTime() + ($block * ShiftCalendarRenderer::SECONDS_PER_ROW);
            $time_slot[] = $this->renderTick($thistime, true);
        }
        return div('lane time', $time_slot);
    }

    /**
     * @param array[] $shifts
     * @return int
     */
    private function calcFirstBlockStartTime($shifts)
    {
        $start_time = $this->shiftsFilter->getEndTime();
        foreach ($shifts as $shift) {
            if ($shift['start'] < $start_time) {
                $start_time = $shift['start'];
            }
        }
        return ShiftCalendarRenderer::SECONDS_PER_ROW * floor(
                ($start_time - ShiftCalendarRenderer::TIME_MARGIN)
                / ShiftCalendarRenderer::SECONDS_PER_ROW
            );
    }

    /**
     * @param array[] $shifts
     * @return int
     */
    private function calcLastBlockEndTime($shifts)
    {
        $end_time = $this->shiftsFilter->getStartTime();
        foreach ($shifts as $shift) {
            if ($shift['end'] > $end_time) {
                $end_time = $shift['end'];
            }
        }

        return ShiftCalendarRenderer::SECONDS_PER_ROW * ceil(
                ($end_time + ShiftCalendarRenderer::TIME_MARGIN)
                / ShiftCalendarRenderer::SECONDS_PER_ROW
            );
    }

    /**
     * @return int
     */
    private function calcBlocksPerSlot()
    {
        return ceil(
            ($this->getLastBlockEndTime() - $this->getFirstBlockStartTime())
            / ShiftCalendarRenderer::SECONDS_PER_ROW
        );
    }

    /**
     * Renders a legend explaining the shift coloring
     *
     * @return string
     */
    private function renderLegend()
    {
        return div('legend mt-3', [
            badge(__('Your shift'), 'primary'),
            badge(__('Help needed'), 'danger'),
            badge(__('Other angeltype needed / collides with my shifts'), 'warning'),
            badge(__('Shift is full'), 'success'),
            badge(__('Shift running/ended or user not arrived/allowed'), 'secondary')
        ]);
    }
}
