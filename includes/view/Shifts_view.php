<?php
/**
 * Calc shift length in format 12:23h.
 * @param Shift $shift
 */
function shift_length($shift) {
  $length = round(($shift['end'] - $shift['start']) / (60 * 60), 0) . ":";
  $length .= str_pad((($shift['end'] - $shift['start']) % (60 * 60)) / 60, 2, "0", STR_PAD_LEFT) . "h";
  return $length;
}
?>