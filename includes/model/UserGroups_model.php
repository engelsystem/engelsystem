<?php

/**
 * Returns users groups
 * @param User $user
 */
function User_groups($user) {
  return sql_select("
      SELECT `Groups`.*
      FROM `UserGroups`
      JOIN `Groups` ON `Groups`.`UID`=`UserGroups`.`group_id`
      WHERE `UserGroups`.`uid`='" . sql_escape($user['UID']) . "'
      ORDER BY `UserGroups`.`group_id`
      ");
}

?>