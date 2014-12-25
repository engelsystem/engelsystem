<?php

function room_link($room) {
  return page_link_to('admin_rooms') . '&show=edit&id=' . $room['RID'];
}

?>