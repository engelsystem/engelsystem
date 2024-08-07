<?php

use Engelsystem\Database\Db;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Illuminate\Database\Eloquent\Collection;

/**
 * Returns all needed angeltypes and already taken needs.
 *
 * @param Shift $shift
 * @return array
 */
function NeededAngelTypes_by_shift($shift)
{
    $needed_angeltypes_source = [];
    // Select from shift
    if (!$shift->schedule) {
        $needed_angeltypes_source = Db::select(
            '
        SELECT
            `needed_angel_types`.*,
            `angel_types`.`name`,
            `angel_types`.`restricted`,
            `angel_types`.`shift_self_signup`
        FROM `needed_angel_types`
        JOIN `angel_types` ON `angel_types`.`id` = `needed_angel_types`.`angel_type_id`
        WHERE `needed_angel_types`.`shift_id` = ?
        ORDER BY `location_id` DESC
        ',
            [$shift->id]
        );
    }

    // Get needed by shift type
    if ($shift->schedule && $shift->schedule->needed_from_shift_type) {
        $needed_angeltypes_source = Db::select('
        SELECT
            `needed_angel_types`.*,
            `angel_types`.`name`,
            `angel_types`.`restricted`,
            `angel_types`.`shift_self_signup`
        FROM `needed_angel_types`
        JOIN `angel_types` ON `angel_types`.`id` = `needed_angel_types`.`angel_type_id`
        WHERE `needed_angel_types`.`shift_type_id` = ?
        ORDER BY `location_id` DESC
        ', [$shift->shift_type_id]);
    }

    // Load from room
    if ($shift->schedule && !$shift->schedule->needed_from_shift_type) {
        $needed_angeltypes_source = Db::select('
        SELECT
            `needed_angel_types`.*,
            `angel_types`.`name`,
            `angel_types`.`restricted`,
            `angel_types`.`shift_self_signup`
        FROM `needed_angel_types`
        JOIN `angel_types` ON `angel_types`.`id` = `needed_angel_types`.`angel_type_id`
        WHERE `needed_angel_types`.`location_id` = ?
        ORDER BY `location_id` DESC
        ', [$shift->location_id]);
    }

    /** @var ShiftEntry[]|Collection $shift_entries */
    $shift_entries = ShiftEntry::with('user', 'angelType')
        ->where('shift_id', $shift->id)
        ->get();
    $needed_angeltypes = [];
    foreach ($needed_angeltypes_source as $angeltype) {
        $angeltype['shift_entries'] = [];
        $angeltype['taken'] = 0;
        foreach ($shift_entries as $shift_entry) {
            if ($shift_entry->angel_type_id == $angeltype['angel_type_id'] && !$shift_entry->freeloaded_by) {
                $angeltype['taken']++;
                $angeltype['shift_entries'][] = $shift_entry;
            }
        }

        $needed_angeltypes[] = $angeltype;
    }

    return $needed_angeltypes;
}
