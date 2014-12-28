<?php

function Room_name_render($room) {
  global $privileges;
  if (in_array('admin_rooms', $privileges))
    return '<a href="' . room_link($room) . '">' . glyph('map-marker') . $room['Name'] . '</a>';
  return glyph('map-marker') . $room['Name'];
}

?>