<?php

/**
 * Returns room id array
 */
function Room_ids() {
  $room_source = sql_select("SELECT `RID` FROM `Room` WHERE `show` = 'Y'");
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
function Room($id) {
  $room_source = sql_select("SELECT * FROM `Room` WHERE `RID`='" . sql_escape($id) . "' AND `show` = 'Y' LIMIT 1");
  if ($room_source === false)
    return false;
  if (count($room_source) > 0)
    return $room_source[0];
  return null;
}

?>
