<?php

namespace Engelsystem;

use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Illuminate\Support\Collection;

class ShiftTableRenderer
{
    /** @var ShiftsFilter */
    private $shiftsFilter;

    /**
     * ShiftTableRenderer constructor.
     *
     * @param Shift[]                   $shifts
     * @param array[]                   $needed_angeltypes
     * @param ShiftEntry[][]|Collection $shift_entries
     * @param ShiftsFilter              $shiftsFilter
     */
    public function __construct(private $shifts, private $needed_angeltypes, private $shift_entries, ShiftsFilter $shiftsFilter)
    {
        $this->shiftsFilter = $shiftsFilter;
    }

    /**
     * Renders the whole table
     *
     * @return string the generated html
     */
    public function render()
    {
        if (count($this->shifts) == 0) {
            return info(__('No shifts found.'), true);
        }
        $shiftTableShiftRenderer = new ShiftTableShiftRenderer();

        $shifts_table = [];
        foreach ($this->shifts as $shift) {
            $shifts_table[] = $shiftTableShiftRenderer->render(
                $shift,
                collect($this->needed_angeltypes[$shift->id]),
                $this->shift_entries[$shift->id],
                auth()->user()
            );
        }

        return div('shift-table table-responsive', [
            '<script>function setStateShiftTableRow(sid, cssclass){
                $("#shift_row_"+sid).parent().addClass("table-"+cssclass)
                $("#shift_row_"+sid).parent().parent().parent().parent().removeClass("table-striped");
}</script>',
            table(
                $this->getTableHeadRows(),
                $shifts_table
            ),
        ]);
    }

    protected function getTableHeadRows(): array
    {
        return [
            'state'           => __('State'),
            'timeslot'        => __('Time and location'),
            'title'           => __('Type and title'),
            'needed_angels'   => __('Needed angels'),
            'selected_angels' => __('Angel'),
        ];
    }
}
