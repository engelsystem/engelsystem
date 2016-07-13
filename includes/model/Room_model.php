<?php

/**
 * Delete a room
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
      `FromPentabarf`='" . sql_escape($from_frab ? 1 : '') . "',
      `show`='" . sql_escape($public ? 1 : '') . "',
      `Number`=0");
  if ($result === false)
    return false;
  return sql_id();
}

/**
 * Returns room by id.
 *
 * @param $id RID
 */
function Room($id) {
  $room_source = sql_select("SELECT * FROM `Room` WHERE `RID`='" . sql_escape($id) . "' AND `show` = 'Y'");

  if ($room_source === false)
    return false;
  if (count($room_source) > 0)
    return $room_source[0];
  return null;
}

?>
