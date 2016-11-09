<?php

/**
 * returns a list of rooms.
 * @param boolean $show_all returns also hidden rooms when true
 */
function Rooms($show_all = false) {
  return sql_select("SELECT * FROM `Room`" . ($show_all ? "" : " WHERE `show`='Y'") . " ORDER BY `Name`");
}

/**
 * Delete a room
 *
 * @param int $room_id          
 */
function Room_delete($room_id) {
  return sql_query("DELETE FROM `Room` WHERE `RID`=" . sql_escape($room_id));
}

/**
 * Create a new room
 *
 * @param string $name
 *          Name of the room
 * @param boolean $from_frab
 *          Is this a frab imported room?
 * @param boolean $public
 *          Is the room visible for angels?
 */
function Room_create($name, $from_frab, $public) {
  $result = sql_query("
      INSERT INTO `Room` SET 
      `Name`='" . sql_escape($name) . "', 
      `FromPentabarf`='" . sql_escape($from_frab ? 'Y' : '') . "', 
      `show`='" . sql_escape($public ? 'Y' : '') . "', 
      `Number`=0");
  if ($result === false) {
    return false;
  }
  return sql_id();
}

/**
 * Returns room by id.
 *
 * @param $room_id RID          
 */
function Room($room_id) {
  $room_source = sql_select("SELECT * FROM `Room` WHERE `RID`='" . sql_escape($room_id) . "' AND `show` = 'Y'");
  
  if ($room_source === false) {
    return false;
  }
  if (count($room_source) > 0) {
    return $room_source[0];
  }
  return null;
}

?>
