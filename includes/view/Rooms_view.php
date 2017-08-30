<?php

use Engelsystem\ShiftCalendarRenderer;
use Engelsystem\ShiftsFilterRenderer;

/**
 * @param array                 $room
 * @param ShiftsFilterRenderer  $shiftsFilterRenderer
 * @param ShiftCalendarRenderer $shiftCalendarRenderer
 * @return string
 */
function Room_view($room, ShiftsFilterRenderer $shiftsFilterRenderer, ShiftCalendarRenderer $shiftCalendarRenderer)
{
    return page_with_title(glyph('map-marker') . $room['Name'], [
        $shiftsFilterRenderer->render($room),
        $shiftCalendarRenderer->render()
    ]);
}

/**
 * @param array $room
 * @return string
 */
function Room_name_render($room)
{
    global $privileges;
    if (in_array('view_rooms', $privileges)) {
        return '<a href="' . room_link($room) . '">' . glyph('map-marker') . $room['Name'] . '</a>';
    }
    return glyph('map-marker') . $room['Name'];
}
