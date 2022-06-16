<?php

use Engelsystem\Database\Db;

/**
 * @param int $user_id
 * @return array
 */
function privileges_for_user($user_id)
{
    $privileges = [];
    $user_privileges = Db::select('
        SELECT `Privileges`.`name`
        FROM `users`
        JOIN `UserGroups` ON (`users`.`id` = `UserGroups`.`uid`)
        JOIN `GroupPrivileges` ON (`UserGroups`.`group_id` = `GroupPrivileges`.`group_id`)
        JOIN `Privileges` ON (`GroupPrivileges`.`privilege_id` = `Privileges`.`id`)
        WHERE `users`.`id`=?
    ', [$user_id]);
    foreach ($user_privileges as $user_privilege) {
        $privileges[] = $user_privilege['name'];
    }
    return $privileges;
}

/**
 * @param int $group_id
 * @return array
 */
function privileges_for_group($group_id)
{
    $privileges = [];
    $groups_privileges = Db::select('
        SELECT `name`
        FROM `GroupPrivileges`
        JOIN `Privileges` ON (`GroupPrivileges`.`privilege_id` = `Privileges`.`id`)
        WHERE `group_id`=?
    ', [$group_id]);
    foreach ($groups_privileges as $guest_privilege) {
        $privileges[] = $guest_privilege['name'];
    }
    return $privileges;
}
