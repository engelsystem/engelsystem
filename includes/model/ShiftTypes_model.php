<?php

use Engelsystem\Database\DB;

/**
 * Delete a shift type.
 *
 * @param int $shifttype_id
 */
function ShiftType_delete($shifttype_id)
{
    DB::delete('DELETE FROM `ShiftTypes` WHERE `id`=?', [$shifttype_id]);
    db_log_delete('shifttypes', $shifttype_id);
}

/**
 * Update a shift type.
 *
 * @param int    $shifttype_id
 * @param string $name
 * @param int    $angeltype_id
 * @param string $description
 */
function ShiftType_update($shifttype_id, $name, $angeltype_id, $description)
{
    DB::update('
      UPDATE `ShiftTypes` SET
      `name`=?,
      `angeltype_id`=?,
      `description`=?,
      `updated_microseconds`=?
      WHERE `id`=?
    ',
        [
            $name,
            $angeltype_id,
            $description,
            time_microseconds(),
            $shifttype_id,
        ]
    );
}

/**
 * Create a shift type.
 *
 * @param string $name
 * @param int    $angeltype_id
 * @param string $description
 * @return int|false new shifttype id
 */
function ShiftType_create($name, $angeltype_id, $description)
{
    DB::insert('
        INSERT INTO `ShiftTypes` (`name`, `angeltype_id`, `description`, `updated_microseconds`)
        VALUES(?, ?, ?, ?)
        ',
        [
            $name,
            $angeltype_id,
            $description,
            time_microseconds(),
        ]
    );

    return DB::getPdo()->lastInsertId();
}

/**
 * Get a shift type by id.
 *
 * @param int $shifttype_id
 * @return array|null
 */
function ShiftType($shifttype_id)
{
    return DB::selectOne('SELECT * FROM `ShiftTypes` WHERE `id`=?', [$shifttype_id]);
}

/**
 * Get all shift types.
 *
 * @return array
 */
function ShiftTypes()
{
    return DB::select('SELECT * FROM `ShiftTypes` ORDER BY `name`');
}
