<?php

use Engelsystem\Database\Db;

/**
 * Entity needed angeltypes describes how many angels of given type are needed for a shift or in a room.
 */

/**
 * Insert a new needed angel type.
 *
 * @param int      $shift_id     The shift. Can be null, but then a room_id must be given.
 * @param int      $angeltype_id The angeltype
 * @param int|null $room_id      The room. Can be null, but then a shift_id must be given.
 * @param int      $count        How many angels are needed?
 *
 * @return int|false
 */
function NeededAngelType_add($shift_id, $angeltype_id, $room_id, $count)
{
    Db::insert('
            INSERT INTO `NeededAngelTypes` ( `shift_id`, `angel_type_id`, `room_id`, `count`)
            VALUES (?, ?, ?, ?)
        ',
        [
            $shift_id,
            $angeltype_id,
            $room_id,
            $count,
        ]);

    return Db::getPdo()->lastInsertId();
}

/**
 * Deletes all needed angel types from given shift.
 *
 * @param int $shift_id id of the shift
 */
function NeededAngelTypes_delete_by_shift($shift_id)
{
    Db::delete('DELETE FROM `NeededAngelTypes` WHERE `shift_id` = ?', [$shift_id]);
}

/**
 * Deletes all needed angel types from given room.
 *
 * @param int $room_id id of the room
 */
function NeededAngelTypes_delete_by_room($room_id)
{
    Db::delete(
        'DELETE FROM `NeededAngelTypes` WHERE `room_id` = ?',
        [$room_id]
    );
}

/**
 * Returns all needed angeltypes by room.
 *
 * @param int $room_id
 * @return array
 */
function NeededAngelTypes_by_room($room_id)
{
    return Db::select(
        'SELECT `angel_type_id`, `count` FROM `NeededAngelTypes` WHERE `room_id`=?',
        [$room_id]
    );
}

/**
 * Returns all needed angeltypes and already taken needs.
 *
 * @param int $shiftId id of shift
 * @return array
 */
function NeededAngelTypes_by_shift($shiftId)
{
    $needed_angeltypes_source = Db::select('
        SELECT
            `NeededAngelTypes`.*,
            `AngelTypes`.`id`,
            `AngelTypes`.`name`,
            `AngelTypes`.`restricted`,
            `AngelTypes`.`no_self_signup`
        FROM `NeededAngelTypes`
        JOIN `AngelTypes` ON `AngelTypes`.`id` = `NeededAngelTypes`.`angel_type_id`
        WHERE `shift_id` = ?
        AND `count` > 0
        ORDER BY `room_id` DESC',
        [$shiftId]
    );

    // Use settings from room
    if (count($needed_angeltypes_source) == 0) {
        $needed_angeltypes_source = Db::select('
        SELECT `NeededAngelTypes`.*, `AngelTypes`.`name`, `AngelTypes`.`restricted`
        FROM `NeededAngelTypes`
        JOIN `AngelTypes` ON `AngelTypes`.`id` = `NeededAngelTypes`.`angel_type_id`
        JOIN `Shifts` ON `Shifts`.`RID` = `NeededAngelTypes`.`room_id`
        WHERE `Shifts`.`SID` = ?
        AND `count` > 0
        ORDER BY `room_id` DESC
        ', [$shiftId]);
    }

    $shift_entries = ShiftEntries_by_shift($shiftId);
    $needed_angeltypes = [];
    foreach ($needed_angeltypes_source as $angeltype) {
        $angeltype['shift_entries'] = [];
        $angeltype['taken'] = 0;
        foreach ($shift_entries as $shift_entry) {
            if ($shift_entry['TID'] == $angeltype['angel_type_id'] && $shift_entry['freeloaded'] == 0) {
                $angeltype['taken']++;
                $angeltype['shift_entries'][] = $shift_entry;
            }
        }

        $needed_angeltypes[] = $angeltype;
    }

    return $needed_angeltypes;
}
