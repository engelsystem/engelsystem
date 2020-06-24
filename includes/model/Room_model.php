<?php

use Engelsystem\Database\DB;
use Engelsystem\ValidationResult;

/**
 * Validate a name for a room.
 *
 * @param string $name    The new name
 * @param int    $room_id The room id
 * @return ValidationResult
 */
function Room_validate_name($name, $room_id)
{
    $valid = true;
    if (empty($name)) {
        $valid = false;
    }

    if (count(DB::select('SELECT RID FROM `Room` WHERE `Name`=? AND NOT `RID`=?', [
            $name,
            $room_id
        ])) > 0) {
        $valid = false;
    }
    return new ValidationResult($valid, $name);
}

/**
 * returns a list of rooms.
 *
 * @return array
 */
function Rooms()
{
    return DB::select('SELECT * FROM `Room` ORDER BY `Name`');
}

/**
 * Returns Room id array
 *
 * @return array
 */
function Room_ids()
{
    $result = DB::select('SELECT `RID` FROM `Room`');
    return select_array($result, 'RID', 'RID');
}

/**
 * Delete a room
 *
 * @param int $room_id
 */
function Room_delete($room_id)
{
    $room = Room($room_id);
    DB::delete('DELETE FROM `Room` WHERE `RID` = ?', [
        $room_id
    ]);
    engelsystem_log('Room deleted: ' . $room['Name']);
}

/**
 * Create a new room
 *
 * @param string  $name      Name of the room
 * @param string  $map_url   URL to a map tha can be displayed in an iframe
 * @param string description Markdown description
 * @return false|int
 */
function Room_create($name, $map_url, $description)
{
    DB::insert('
            INSERT INTO `Room` (`Name`, `map_url`, `description`)
            VALUES (?, ?, ?)
        ', [
        $name,
        $map_url,
        $description
    ]);
    $result = DB::getPdo()->lastInsertId();

    engelsystem_log(
        'Room created: ' . $name
        . ', map_url: ' . $map_url
        . ', description: ' . $description
    );

    return $result;
}

/**
 * Update a room
 *
 * @param int     $room_id     The rooms id
 * @param string  $name        Name of the room
 * @param string  $map_url     URL to a map tha can be displayed in an iframe
 * @param string  $description Markdown description
 * @return int
 */
function Room_update($room_id, $name, $map_url, $description)
{
    $result = DB::update('
        UPDATE `Room`
        SET
            `Name`=?,
            `map_url`=?,
            `description`=?
        WHERE `RID`=?
        LIMIT 1', [
        $name,
        $map_url,
        $description,
        $room_id
    ]);

    engelsystem_log(
        'Room updated: ' . $name .
        ', map_url: ' . $map_url .
        ', description: ' . $description
    );

    return $result;
}

/**
 * Returns room by id.
 *
 * @param int $room_id RID
 * @return array|null
 */
function Room($room_id)
{
    $room = DB::selectOne('
        SELECT *
        FROM `Room`
        WHERE `RID` = ?', [
        $room_id
    ]);

    return empty($room) ? null : $room;
}
