<?php

use Engelsystem\Database\DB;

/**
 * returns a list of rooms.
 *
 * @param boolean $show_all returns also hidden rooms when true
 * @return array
 */
function Rooms($show_all = false)
{
    return DB::select('SELECT * FROM `Room`' . ($show_all ? '' : ' WHERE `show`=\'Y\'') . ' ORDER BY `Name`');
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
 * @param boolean $public    Is the room visible for angels?
 * @param int     $number    Room number
 * @return false|int
 */
function Room_create($name, $from_frab, $public, $number = null)
{
    DB::insert('
          INSERT INTO `Room` (`Name`, `FromPentabarf`, `show`, `Number`)
           VALUES (?, ?, ?, ?)
        ',
        [
            $name,
            $from_frab ? 'Y' : '',
            $public ? 'Y' : '',
            (int)$number,
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
function Room($room_id, $onlyVisible = true)
{
    $room_source = DB::select('
        SELECT *
        FROM `Room`
        WHERE `RID` = ?
        ' . ($onlyVisible ? 'AND `show` = \'Y\'' : ''),
        [$room_id]
    );

    if (DB::getStm()->errorCode() != '00000') {
        return false;
    }

    if (empty($room_source)) {
        return null;
    }

    return array_shift($room_source);
}
