<?php

/**
 * Returns all shift entries in given shift for given angeltype.
 * @param int $shift_id
 * @param int $angeltype_id
 */
function ShiftEntries_by_shift_and_angeltype($shift_id, $angeltype_id) {
  return sql_select("
      SELECT * 
      FROM `ShiftEntry`
      WHERE `SID`=" . sql_escape($shift_id) . "
      AND `TID`=" . sql_escape($angeltype_id) . "
      ");
}

?>