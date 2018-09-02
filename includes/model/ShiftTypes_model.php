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
      `description`=?
      WHERE `id`=?
    ',
        [
            $name,
            $angeltype_id,
            $description,
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
        INSERT INTO `ShiftTypes` (`name`, `angeltype_id`, `description`)
        VALUES(?, ?, ?)
        ',
        [
            $name,
            $angeltype_id,
            $description
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
    $shiftType = DB::selectOne('SELECT * FROM `ShiftTypes` WHERE `id`=?', [$shifttype_id]);

    return empty($shiftType) ? null : $shiftType;
}

/**
 * Get all shift types.
 *
 * @return array[]
 */
function ShiftTypes()
{
    return DB::select('SELECT * FROM `ShiftTypes` ORDER BY `name`');
}
