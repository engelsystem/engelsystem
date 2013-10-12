<?php

/**
 * Load a string by key.
 *
 * @param string $textid
 * @param string $sprache
 */
function Sprache($textid, $sprache) {
  $sprache_source = sql_select("
      SELECT *
      FROM `Sprache`
      WHERE `TextID`='" . sql_escape($textid) . "'
      AND `Sprache`='" . sql_escape($sprache) . "'
      LIMIT 1
      ");
  if ($sprache_source === false)
    return false;
  if (count($sprache_source) == 1)
    return $sprache_source[0];
  return null;
}

?>