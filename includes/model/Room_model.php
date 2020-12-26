<?php

use Engelsystem\Models\Room;
use Engelsystem\ValidationResult;
use Illuminate\Support\Collection;

/**
 * Validate a name for a room.
 *
 * @param string $name    The new name
 * @param int    $room_id The room id
 * @return ValidationResult
 */
function Room_validate_name(string $name, int $room_id)
{
    $valid = true;
    if (empty($name)) {
        $valid = false;
    }

    $roomCount = Room::query()
        ->where('name', $name)
        ->where('id', '!=', $room_id)
        ->count();
    if ($roomCount) {
        $valid = false;
    }

    return new ValidationResult($valid, $name);
}

/**
 * returns a list of rooms.
 *
 * @return Room[]|Collection
 */
function Rooms()
{
    return Room::orderBy('name')->get();
}

/**
 * Returns Room id array
 *
 * @return int[]
 */
function Room_ids()
{
    return Room::query()
        ->select('id')
        ->pluck('id')
        ->toArray();
}

/**
 * Delete a room
 *
 * @param Room $room
 */
function Room_delete(Room $room)
{
    $room->delete();
    engelsystem_log('Room deleted: ' . $room->name);
}

/**
 * Create a new room
 *
 * @param string      $name    Name of the room
 * @param string|null $map_url URL to a map tha can be displayed in an iframe
 * @param string|null $description
 *
 * @return null|int
 */
function Room_create(string $name, string $map_url = null, string $description = null)
{
    $room = new Room();
    $room->name = $name;
    $room->description = $description;
    $room->map_url = $map_url;
    $room->save();

    engelsystem_log(
        'Room created: ' . $name
        . ', map_url: ' . $map_url
        . ', description: ' . $description
    );

    return $room->id;
}

/**
 * Update a room
 *
 * @param int         $room_id     The rooms id
 * @param string      $name        Name of the room
 * @param string|null $map_url     URL to a map tha can be displayed in an iframe
 * @param string|null $description Markdown description
 */
function Room_update(int $room_id, string $name, string $map_url = null, string $description = null)
{
    $room = Room::find($room_id);
    $room->name = $name;
    $room->description = $description ?: null;
    $room->map_url = $map_url ?: null;
    $room->save();

    engelsystem_log(
        'Room updated: ' . $name .
        ', map_url: ' . $map_url .
        ', description: ' . $description
    );
}
