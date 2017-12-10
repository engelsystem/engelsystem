<?php

use Engelsystem\Database\DB;

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
    DB::delete('DELETE FROM `Room` WHERE `RID` = ?', [$room_id]);
}

/**
 * Create a new room
 *
 * @param string  $name      Name of the room
 * @param boolean $from_frab Is this a frab imported room?
 * @param string $map_url URL to a map tha can be displayed in an iframe
 * @param description markdown description
 * @return false|int
 */
function Room_create($name, $from_frab, $map_url, $description)
{
    DB::insert('
          INSERT INTO `Room` (`Name`, `from_frab`, `map_url`, `description`)
           VALUES (?, ?, ?, ?)
        ',
        [
            $name,
            (int) $from_frab,
            $map_url,
            $description,
        ]
    );

    return DB::getPdo()->lastInsertId();
}

/**
 * Returns room by id.
 *
 * @param int  $room_id RID
 * @param bool $onlyVisible
 * @return array|false
 */
function Room($room_id)
{
    return DB::selectOne('
        SELECT *
        FROM `Room`
        WHERE `RID` = ?',
        [$room_id]
    );
}
