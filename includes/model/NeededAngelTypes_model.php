<?php

use Engelsystem\Database\Db;
use Engelsystem\Models\Shifts\ShiftEntry;
use Illuminate\Database\Eloquent\Collection;

/**
 * Returns all needed angeltypes and already taken needs.
 *
 * @param int $shiftId id of shift
 * @return array
 */
function NeededAngelTypes_by_shift($shiftId)
{
    $needed_angeltypes_source = Db::select(
        '
        SELECT
            `needed_angel_types`.*,
            `angel_types`.`id`,
            `angel_types`.`name`,
            `angel_types`.`restricted`,
            `angel_types`.`no_self_signup`
        FROM `needed_angel_types`
        JOIN `angel_types` ON `angel_types`.`id` = `needed_angel_types`.`angel_type_id`
        WHERE `shift_id` = ?
        ORDER BY `room_id` DESC',
        [$shiftId]
    );

    // Use settings from room
    if (count($needed_angeltypes_source) == 0) {
        $needed_angeltypes_source = Db::select('
        SELECT `needed_angel_types`.*, `angel_types`.`name`, `angel_types`.`restricted`
        FROM `needed_angel_types`
        JOIN `angel_types` ON `angel_types`.`id` = `needed_angel_types`.`angel_type_id`
        JOIN `shifts` ON `shifts`.`room_id` = `needed_angel_types`.`room_id`
        WHERE `shifts`.`id` = ?
        ORDER BY `room_id` DESC
        ', [$shiftId]);
    }

    /** @var ShiftEntry[]|Collection $shift_entries */
    $shift_entries = ShiftEntry::with('user', 'angelType')
        ->where('shift_id', $shiftId)
        ->get();
    $needed_angeltypes = [];
    foreach ($needed_angeltypes_source as $angeltype) {
        $angeltype['shift_entries'] = [];
        $angeltype['taken'] = 0;
        foreach ($shift_entries as $shift_entry) {
            if ($shift_entry->angel_type_id == $angeltype['angel_type_id'] && !$shift_entry->freeloaded) {
                $angeltype['taken']++;
                $angeltype['shift_entries'][] = $shift_entry;
            }
        }

        $needed_angeltypes[] = $angeltype;
    }

    return $needed_angeltypes;
}
