<?php

use Engelsystem\Database\DB;

/**
 * Returns users groups
 *
 * @param int $userId
 * @return array[]
 */
function User_groups($userId)
{
    return DB::select('
          SELECT `Groups`.*
          FROM `UserGroups`
          JOIN `Groups` ON `Groups`.`UID`=`UserGroups`.`group_id`
          WHERE `UserGroups`.`uid`=?
          ORDER BY `UserGroups`.`group_id`
       ',
        [$userId]
    );
}
