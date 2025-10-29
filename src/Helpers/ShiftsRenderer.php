<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Carbon\Carbon;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\ShiftCalendarRenderer;
use Engelsystem\ShiftsFilter;
use Illuminate\Support\Collection;

class ShiftsRenderer
{
    /**
     * This is glue code to force the legacy shift renderer to the new code
     *
     * @param Collection|Shift[] $shifts
     */
    public function render(Collection | array $shifts): string
    {
        /** @var array[] $neededAngelTypes */
        $neededAngelTypes = [];
        /** @var ShiftEntry[][] $shiftEntries */
        $shiftEntries = [];

        foreach ($shifts as $shift) {
            $shiftEntries[$shift->id] = $shift->shiftEntries;

            if (!$shift->schedule) {
                $angelTypes = $shift->neededAngelTypes;
            } else {
                if ($shift->schedule->needed_from_shift_type) {
                    $angelTypes = $shift->shiftType->neededAngelTypes;
                } else {
                    $angelTypes = $shift->location->neededAngelTypes;
                }
            }

            $neededAngelTypes[$shift->id] = [];
            foreach ($angelTypes as $nAngelType) {
                $data = $nAngelType->toArray();
                $data['id'] = $nAngelType->angelType->id;
                $data['name'] = $nAngelType->angelType->name;
                $data['restricted'] = $nAngelType->angelType->restricted;
                $data['shift_self_signup'] = $nAngelType->angelType->shift_self_signup;
                $neededAngelTypes[$shift->id][] = $data;
            }
        }

        return $this->renderShiftCalendar($shifts, $neededAngelTypes, $shiftEntries);
    }

    /**
     * @param Collection|Shift[] $shifts
     * @param array[] $neededAngelTypes
     * @param ShiftEntry[][] $shiftEntries
     * @codeCoverageIgnore
     */
    protected function renderShiftCalendar(
        array | Collection $shifts,
        array $neededAngelTypes,
        array $shiftEntries
    ): string {
        if (!$shifts instanceof Collection) {
            $shifts = collect($shifts);
        }

        /** @var Carbon $start */
        $start = $shifts->min('start');
        /** @var Carbon $end */
        $end = $shifts->max('end');

        $shiftsFilter = new ShiftsFilter();
        $shiftsFilter->setStartTime($start ? $start->timestamp : 0);
        $shiftsFilter->setEndTime($end ? $end->timestamp : 0);

        $renderer = new ShiftCalendarRenderer($shifts, $neededAngelTypes, $shiftEntries, $shiftsFilter);

        return $renderer->render();
    }
}
