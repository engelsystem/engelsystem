<?php

use Engelsystem\Database\DB;

/**
 * Delete a shift type.
 *
 * @param int $shifttype_id
 * @return bool
 */
function ShiftType_delete($shifttype_id)
{
    return DB::delete('DELETE FROM `ShiftTypes` WHERE `id`=?', [$shifttype_id]);
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
    $shifttype = DB::select('SELECT * FROM `ShiftTypes` WHERE `id`=?', [$shifttype_id]);
    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to load shift type.');
    }
    if (empty($shifttype)) {
        return null;
    }
    return array_shift($shifttype);
}

/**
 * Get all shift types.
 *
 * @return array|false
 */
function ShiftTypes()
{
    $result = DB::select('SELECT * FROM `ShiftTypes` ORDER BY `name`');

    if (DB::getStm()->errorCode() != '00000') {
        return false;
    }

    return $result;
}
