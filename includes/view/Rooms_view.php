<?php
use Engelsystem\ShiftsFilterRenderer;
use Engelsystem\ShiftCalendarRenderer;

function Room_view($room, ShiftsFilterRenderer $shiftsFilterRenderer, ShiftCalendarRenderer $shiftCalendarRenderer) {
  return page_with_title(glyph('map-marker') . $room['Name'], [
      $shiftsFilterRenderer->render(room_link($room)) ,
      $shiftCalendarRenderer->render()
  ]);
}

function Room_name_render($room) {
  global $privileges;
  if (in_array('view_rooms', $privileges)) {
    return '<a href="' . room_link($room) . '">' . glyph('map-marker') . $room['Name'] . '</a>';
  }
  return glyph('map-marker') . $room['Name'];
}

?>