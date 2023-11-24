<?php

namespace Engelsystem;

use Engelsystem\Helpers\Carbon;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Illuminate\Support\Collection;

class ShiftCalendarRenderer
{
    /**
     * 15m * 60s/m = 900s
     */
    public const SECONDS_PER_ROW = 900;

    /**
     * Height of a block in pixel.
     * Do not change - corresponds with theme/css
     */
    public const BLOCK_HEIGHT = 30;

    /**
     * Distance between two shifts in pixels
     */
    public const MARGIN = 5;

    /**
     * Seconds added to the start and end time
     */
    public const TIME_MARGIN = 1800;

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

    /**
     * ShiftCalendarRenderer constructor.
     *
     * @param Shift[]                 $shifts
     * @param array[]                 $needed_angeltypes
     * @param ShiftEntry[][]|Collection $shift_entries
     * @param ShiftsFilter            $shiftsFilter
     */
    public function __construct($shifts, private $needed_angeltypes, private $shift_entries, ShiftsFilter $shiftsFilter)
    {
        $this->shiftsFilter = $shiftsFilter;
        $this->firstBlockStartTime = $this->calcFirstBlockStartTime($shifts);
        $this->lastBlockEndTime = $this->calcLastBlockEndTime($shifts);
        $this->lanes = $this->assignShiftsToLanes($shifts);
    }

    /**
     * Assigns the shifts to different lanes per location if they collide
     *
     * @param Shift[] $shifts The shifts to assign
     * @return array Returns an array that assigns a location_id to an array of ShiftCalendarLane containing the shifts
     */
    private function assignShiftsToLanes($shifts)
    {
        // array that assigns a location id to a list of lanes (per location)
        $lanes = [];

        foreach ($shifts as $shift) {
            $location = $shift->location;
            $header = location_name_render($location);
            if (!isset($lanes[$location->id])) {
                // initialize location with one lane
                $lanes[$location->id] = [
                    new ShiftCalendarLane($header),
                ];
            }
            // Try to add the shift to the existing lanes for this location
            $shift_added = false;
            foreach ($lanes[$location->id] as $lane) {
                /** @var ShiftCalendarLane $lane */
                if ($lane->shiftFits($shift)) {
                    $lane->addShift($shift);
                    $shift_added = true;
                    break;
                }
            }
            // If all lanes for this location are busy, create a new lane and add shift to it
            if (!$shift_added) {
                $newLane = new ShiftCalendarLane($header);
                $newLane->addShift($shift);
                $lanes[$location->id][] = $newLane;
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
                $this->renderShiftLanes(),
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
        foreach ($this->lanes as $location_lanes) {
            foreach ($location_lanes as $lane) {
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
            while ($rendered_until + ShiftCalendarRenderer::SECONDS_PER_ROW <= $shift->start->timestamp) {
                $html .= $this->renderTick($rendered_until);
                $rendered_until += ShiftCalendarRenderer::SECONDS_PER_ROW;
            }

            $needed_angeltypes = collect($this->needed_angeltypes[$shift->id]);

            // Add angel types from shift entries without reference from needed angel types
            foreach (
                $shift->shiftEntries
                    ->whereNotIn('angel_type_id', $needed_angeltypes->pluck('id'))
                    ->groupBy('angel_type_id') as $shiftEntriesOfAngelType
            ) {
                /** @var Collection|ShiftEntry[] $shiftEntriesOfAngelType */
                /** @var AngelType $angeltype */
                $angeltype = $shiftEntriesOfAngelType->first()->angelType;
                $needed_angeltypes[] = [
                    'id' => $angeltype->id,
                    'location_id' => null,
                    'shift_id' => $shift->id,
                    'shift_type_id' => null,
                    'angel_type_id' => $angeltype->id,
                    'count' => $shift->shiftEntries
                        ->where('angel_type_id', $angeltype->id)
                        ->whereNull('freeloaded_by')
                        ->count(),
                    'name' => $angeltype->name,
                    'restricted' => $angeltype->restricted,
                    'shift_self_signup' => $angeltype->shift_self_signup,
                ];
            }

            list ($shift_height, $shift_html) = $shift_renderer->render(
                $shift,
                $needed_angeltypes,
                $this->shift_entries[$shift->id],
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
            $html,
        ]);
    }

    /**
     * Renders a tick/block for given time
     *
     * @param int     $time unix timestamp
     * @param boolean $label Should time labels be generated?
     * @return string rendered tick html
     */
    private function renderTick($time, $label = false)
    {
        $time = Carbon::createFromTimestamp($time, Carbon::now()->timezone);
        $class = $label ? 'tick bg-' . theme_type() : 'tick ';

        $diffNow = $time->diffInMinutes() * 60;
        if ($diffNow >= 0 && $diffNow < self::SECONDS_PER_ROW) {
            $class .= ' now';
        }

        if ($time->isStartOfDay()) {
            if (!$label) {
                return div($class . ' day');
            }
            return div($class . ' day', [
                $time->format(__('m-d')) . '<br>' . $time->format(__('H:i')),
            ]);
        } elseif ($time->isStartOfHour()) {
            if (!$label) {
                return div($class . ' hour');
            }
            return div($class . ' hour', [
                $time->format(__('m-d')) . '<br>' . $time->format(__('H:i')),
            ]);
        }
        return div($class);
    }

    /**
     * Renders the left time lane including hour/day ticks
     *
     * @return string
     */
    private function renderTimeLane()
    {
        $bg = 'bg-' . theme_type();

        $time_slot = [
            div('header ' . $bg, [
                __('log.time'),
            ]),
        ];
        for ($block = 0; $block < $this->getBlocksPerSlot(); $block++) {
            $thistime = $this->getFirstBlockStartTime() + ($block * ShiftCalendarRenderer::SECONDS_PER_ROW);
            $time_slot[] = $this->renderTick($thistime, true);
        }
        return div('lane time', $time_slot);
    }

    /**
     * @param Shift[] $shifts
     * @return int
     */
    private function calcFirstBlockStartTime($shifts)
    {
        $start_time = $this->shiftsFilter->getEndTime();
        foreach ($shifts as $shift) {
            if ($shift->start->timestamp < $start_time) {
                $start_time = $shift->start->timestamp;
            }
        }
        return ShiftCalendarRenderer::SECONDS_PER_ROW * floor(
            ($start_time - ShiftCalendarRenderer::TIME_MARGIN)
                / ShiftCalendarRenderer::SECONDS_PER_ROW
        );
    }

    /**
     * @param Shift[] $shifts
     * @return int
     */
    private function calcLastBlockEndTime($shifts)
    {
        $end_time = $this->shiftsFilter->getStartTime();
        foreach ($shifts as $shift) {
            if ($shift->end->timestamp > $end_time) {
                $end_time = $shift->end->timestamp;
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
        return (int) ceil(
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
            badge(__('Other angel type needed / collides with my shifts'), 'warning'),
            badge(__('Shift is full'), 'success'),
            badge(__('Shift is running/has ended, you have not arrived or signup is blocked otherwise'), 'secondary'),
        ]);
    }
}
