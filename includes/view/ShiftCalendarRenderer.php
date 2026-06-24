<?php

namespace Engelsystem;

use Carbon\Carbon;
use DASPRiD\Enum\Exception\IllegalArgumentException;
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

    /** @var int */
    private $blocksPerSlot = null;

    /** @var array */
    private $startAndEndPerDay;

    /** @var Carbon */
    private $calendarStart;

    /** @var Carbon */
    private $calendarEnd;

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
        $this->lanes = $this->assignShiftsToLanes($shifts);

        if (config('shift_view.enable_compact_view')) {
            $this->startAndEndPerDay = self::collectStartAndEndPerDay($shifts);
            list($this->calendarStart, $this->calendarEnd) = $this->getCalendarStartAndEnd();
        } else {
            $this->startAndEndPerDay = self::mergeTimeSpanIntoList([], $shiftsFilter->getStart(), $shiftsFilter->getEnd());
            $this->calendarStart     = $shiftsFilter->getStart();
            $this->calendarEnd       = $shiftsFilter->getEnd();
        }
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
     * Merges the provides time range [$start, $end] into a list of existing time spans per day.
     *
     * @param Carbon[][] $times  List of existing time spans per day
     * @param Carbon $start      Start datetime of the to-be-added time span
     * @param Carbon $end        End datetime of the to-be-added time span
     * @return Carbon[][]        Modified list of time spans per day
     */
    public static function mergeSingleDayTimeSpanIntoList(array $times, Carbon $start, Carbon $end): array
    {
        $dateStr = self::getDateStr($start);
        if ($start->dayOfMillennium != $end->dayOfMillennium) {
            throw new IllegalArgumentException('start and end must be on the same day');
        }

        if (!array_key_exists($dateStr, $times)) {
            $times[$dateStr] = [$start, $end];
        } else {
            $times[$dateStr][0] = min($times[$dateStr][0], $start);
            $times[$dateStr][1] = max($times[$dateStr][1], $end);
        }

        return $times;
    }

    /**
     * Merges the provides time range [$start, $end] into a list of existing time spans per day.
     * The provided time range may span multiple days.
     *
     * @param Carbon[][] $times  List of existing time spans per day
     * @param Carbon $start      Start datetime of the to-be-added time span
     * @param Carbon $end        End datetime of the to-be-added time span
     * @return Carbon[][]        Modified list of time spans per day
     */
    public static function mergeTimeSpanIntoList(array $times, Carbon $start, Carbon $end): array
    {
        if ($start->dayOfMillennium != $end->dayOfMillennium) {
            // Handle the start day: Start at shift start, end at (roughly) midnight
            $startDayEnd = $start->clone();
            $startDayEnd->setTime(23, 59);

            $times = self::mergeSingleDayTimeSpanIntoList($times, $start, $startDayEnd);

            // If a time span spans multiple days, include the whole days between the day start and end
            $daysBetween = $start->daysUntil($end->clone()->setTimeFrom($start));
            $daysBetween->excludeStartDate(true);
            $daysBetween->excludeEndDate(true);
            foreach ($daysBetween as $date) {
                $dayStart = $date->clone();
                $dayStart->minuteOfDay(0);

                $dayEnd = $date->clone();
                $dayEnd->setTime(23, 59);

                $times = self::mergeSingleDayTimeSpanIntoList($times, $dayStart, $dayEnd);
            }

            // Handle the end day: Start at midnight, end at the shift end
            $endDayStart = $end->clone();
            $endDayStart->minuteOfDay(0);

            $times = self::mergeSingleDayTimeSpanIntoList($times, $endDayStart, $end);
        } else {
            // The simple case with a shift starting and ending on the same day
            $times = self::mergeSingleDayTimeSpanIntoList($times, $start, $end);
        }
        return $times;
    }

    /**
     * @param Shift[] $shifts The shifts to analyse and assign to days
     * @return array An array of date => [startdatetime; enddatetime]
     */
    public static function collectStartAndEndPerDay($shifts): array
    {
        $times = [];
        foreach ($shifts as $shift) {
            $times = self::mergeTimeSpanIntoList($times, $shift->start, $shift->end);
        }

        // extend time spans per day by 30 minutes earlier/later, rounded to 15 minute blocks
        foreach ($times as $interval) {
            $startMinOfDay = $interval[0]->minuteOfDay;
            $interval[0]->minuteOfDay(max(0, $startMinOfDay - 30 - (($startMinOfDay - 30) % 15)));

            $endMinOfDay = $interval[1]->minuteOfDay;
            $interval[1]->minuteOfDay(min(23 * 60 + 59, $endMinOfDay + 30 + (($endMinOfDay + 30) % 15) - 1));
        }

        return $times;
    }


    private function getStartAndEnd(Carbon $date): array
    {
        $dateStr = self::getDateStr($date);
        return array_key_exists($dateStr, $this->startAndEndPerDay) ? $this->startAndEndPerDay[$dateStr] : [null, null];
    }

    private function getCalendarStartAndEnd(): array
    {
        $startsAndEnds = array_merge(...array_values($this->startAndEndPerDay));
        return empty($startsAndEnds) ? [null, null] : [min(...$startsAndEnds), max(...$startsAndEnds)];
    }

    /**
     * @return string
     */
    private static function getDateStr($carbon)
    {
        return sprintf('%04d-%02d-%02d', $carbon->year, $carbon->month, $carbon->day);
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

        $lanes = [];

        if (config('shift_view.enable_date_lane')) {
            $lanes[] = $this->renderDateLane();
        }

        $lanes[] = $this->renderTimeLane();
        $lanes[] = $this->renderShiftLanes();

        return div('shift-calendar table-responsive', $lanes) . $this->renderLegend();
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
        $rendered_until = $this->calendarStart->getTimestamp();
        $previous_tick = 0;

        foreach ($lane->getShifts() as $shift) {
            while ($rendered_until + ShiftCalendarRenderer::SECONDS_PER_ROW <= $shift->start->timestamp) {
                if ($this->shouldRenderTick($rendered_until)) {
                    $html .= $this->renderTick($rendered_until, $previous_tick);
                    $previous_tick = $rendered_until;
                }
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

        while ($rendered_until < $this->calendarEnd->getTimestamp()) {
            if ($this->shouldRenderTick($rendered_until)) {
                $html .= $this->renderTick($rendered_until, $previous_tick);
                $previous_tick = $rendered_until;
            }
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
    private function renderTick($time, $previousTickTimestamp, $label = false)
    {
        $previousTickTime = Carbon::createFromTimestamp($previousTickTimestamp, Carbon::now()->timezone);
        $time = Carbon::createFromTimestamp($time, Carbon::now()->timezone);
        $class = $label ? 'tick bg-' . theme_type() : 'tick ';

        $diffNow = $time->diffInMinutes() * 60;
        if ($diffNow >= 0 && $diffNow < self::SECONDS_PER_ROW) {
            $class .= ' now';
        }

        if ($time->isStartOfDay() || $time->dayOfYear != $previousTickTime->dayOfYear) {
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
        $previous_tick = 0;
        for ($block = 0; $block < $this->getBlocksPerSlot(); $block++) {
            $thistime = $this->calendarStart->getTimestamp() + ($block * ShiftCalendarRenderer::SECONDS_PER_ROW);

            if ($this->shouldRenderTick($thistime)) {
                $time_slot[] = $this->renderTick($thistime, $previous_tick, true);
                $previous_tick = $thistime;
            }
        }
        return div('lane time', $time_slot);
    }

    private function shouldRenderTick($thistime): bool
    {
        $thistimeCarbon = Carbon::createFromTimestamp($thistime, Carbon::now()->timezone);
        $dateStr = $this->getDateStr($thistimeCarbon);

        if (array_key_exists($dateStr, $this->startAndEndPerDay)) {
            list($startOfDay, $endOfDay) = $this->startAndEndPerDay[$dateStr];

            return $startOfDay <= $thistimeCarbon && $thistimeCarbon < $endOfDay;
        }
        return false;
    }

    /**
     * Renders the left date lane
     *
     * @return string
     */
    private function renderDateLane()
    {
        $bg = 'bg-' . theme_type();

        $time_slot = [
            div('header ' . $bg, []),
        ];

        $startTime = $this->calendarStart->clone();
        $endDate = $this->getDateStr($this->calendarEnd);

        $first = true;
        $current_time = $startTime;
        do {
            $day = $this->getDateStr($current_time);
            list($startOfDay, $endOfDay) = $this->getStartAndEnd($current_time);

            if ($startOfDay != null) {
                if ($first) {
                    $startOfDay = $startTime;
                    $first = false;
                }
                $minutesPerRow = ShiftCalendarRenderer::SECONDS_PER_ROW / 60;
                $num_rows = $startOfDay->floorMinute($minutesPerRow)->diffInSeconds($endOfDay->ceilMinute($minutesPerRow)) / ShiftCalendarRenderer::SECONDS_PER_ROW;

                $time_slot[] = $this->renderDayTick($startOfDay, $num_rows);
            }

            $current_time->addDay();
        } while ($day != $endDate);

        return div('lane date', $time_slot);
    }

    private function renderDayTick($time, $blocks)
    {
        $class = 'dow bg-' . theme_type();

        return div($class, [
            __($time->format('l')) . ', ' . $time->format(__('m-d')),
        ], '', 'style="height: ' . ($blocks * 30) . 'px"');
    }

    /**
     * @return int
     */
    private function calcBlocksPerSlot()
    {
        return (int) ceil(
            ($this->calendarEnd->getTimestamp() - $this->calendarStart->getTimestamp())
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
