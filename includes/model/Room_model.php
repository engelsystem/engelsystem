<?php

/**
 * Returns room id array
 */
function mRoomList() {
  $room_source = sql_select("SELECT `RID` FROM `Room`");
  if ($room_source === false)
    return false;
  if (count($room_source) > 0)
    return $room_source;
  return null;
}


/**
 * Returns room by id.
 *
 * @param $id RID
 */
function mRoom($id) {
  $room_source = sql_select("SELECT * FROM `Room` WHERE `RID`=" . sql_escape($id) . " LIMIT 1");
  if ($room_source === false)
    return false;
  if (count($room_source) > 0)
    return $room_source[0];
  return null;
}


?>
