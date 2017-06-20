<?php

use Engelsystem\Database\DB;

/**
 * Returns users groups
 *
 * @param array $user
 * @return array
 */
function User_groups($user)
{
    return DB::select('
          SELECT `Groups`.*
          FROM `UserGroups`
          JOIN `Groups` ON `Groups`.`UID`=`UserGroups`.`group_id`
          WHERE `UserGroups`.`uid`=?
          ORDER BY `UserGroups`.`group_id`
       ',
        [$user['UID']]
    );
}
