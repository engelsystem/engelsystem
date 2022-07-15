<?php

namespace Engelsystem;

use Engelsystem\Models\Room;

class ShiftTableRenderer
{

    /** @var ShiftsFilter */
    private $shiftsFilter;

    /** @var array[] */
    private $needed_angeltypes;

    /** @var array[] */
    private $shift_entries;

    /**
     * ShiftCalendarRenderer constructor.
     *
     * @param array[] $shifts
     * @param array[] $needed_angeltypes
     * @param array[] $shift_entries
     * @param ShiftsFilter $shiftsFilter
     */
    public function __construct($shifts, $needed_angeltypes, $shift_entries, ShiftsFilter $shiftsFilter)
    {
        $this->shiftsFilter = $shiftsFilter;
        $this->needed_angeltypes = $needed_angeltypes;
        $this->shift_entries = $shift_entries;
        $this->shifts = $shifts;
    }

    /**
     * Renders the whole calendar
     *
     * @return string the generated html
     */
    public function render()
    {
        if (count($this->shifts) == 0) {
            return info(__('No shifts found.'), true);
        }
        $shiftTableRenderer = new ShiftTableShiftRenderer();

        $shifts_table = [];
        foreach ($this->shifts as $shift) {
            $shifts_table_entry = $shiftTableRenderer->render(
                $shift,
                $this->needed_angeltypes[$shift['SID']],
                $this->shift_entries[$shift['SID']],
                auth()->user()
            );
            $shifts_table[] = $shifts_table_entry;
        }

        return div('shift-table table-responsive', [
            table(
                $this->getTableHeadRows(),
                $shifts_table
            )
        ]);
    }

    protected function getTableHeadRows(): array
    {
        return [
            'timeslot'        => __('Time and location'),
            'title'           => __('Type and title'),
            'needed_angels'   => __('Needed angels'),
            'selected_angels' => __('Angels')
        ];
    }

}
