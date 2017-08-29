<?php

use Engelsystem\Database\DB;

/**
 *  Testet ob ein User eingeloggt ist und lädt die entsprechenden Privilegien
 */
function load_auth()
{
    global $user, $privileges;

    $user = null;
    if (isset($_SESSION['uid'])) {
        $user = DB::select('SELECT * FROM `User` WHERE `UID`=? LIMIT 1', [$_SESSION['uid']]);
        if (count($user) > 0) {
            // User ist eingeloggt, Datensatz zur Verfügung stellen und Timestamp updaten
            $user = array_shift($user);
            DB::update('
                UPDATE `User`
                SET `lastLogIn` = ?
                WHERE `UID` = ?
                LIMIT 1
            ', [
                time(),
                $_SESSION['uid'],
            ]);
            $privileges = privileges_for_user($user['UID']);
            return;
        }
        unset($_SESSION['uid']);
    }

    // guest privileges
    $privileges = privileges_for_group(-10);
}

/**
 * generate a salt (random string) of arbitrary length suitable for the use with crypt()
 *
 * @param int $length
 * @return string
 */
function generate_salt($length = 16)
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
    $salt = '';
    for ($i = 0; $i < $length; $i++) {
        $salt .= $alphabet[rand(0, strlen($alphabet) - 1)];
    }
    return $salt;
}

/**
 * set the password of a user
 *
 * @param int    $uid
 * @param string $password
 * @return bool
 */
function set_password($uid, $password)
{
    $result = DB::update('
        UPDATE `User`
        SET `Passwort` = ?,
        `password_recovery_token`=NULL
        WHERE `UID` = ?
        LIMIT 1
        ',
        [
            crypt($password, config('crypt_alg') . '$' . generate_salt(16) . '$'),
            $uid
        ]
    );
    if (DB::getStm()->errorCode() != '00000') {
        engelsystem_error('Unable to update password.');
    }
    return $result;
}

/**
 * verify a password given a precomputed salt.
 * if $uid is given and $salt is an old-style salt (plain md5), we convert it automatically
 *
 * @param string $password
 * @param string $salt
 * @param int    $uid
 * @return bool
 */
function verify_password($password, $salt, $uid = null)
{
    $crypt_alg = config('crypt_alg');
    $correct = false;
    if (substr($salt, 0, 1) == '$') { // new-style crypt()
        $correct = crypt($password, $salt) == $salt;
    } elseif (substr($salt, 0, 7) == '{crypt}') { // old-style crypt() with DES and static salt - not used anymore
        $correct = crypt($password, '77') == $salt;
    } elseif (strlen($salt) == 32) { // old-style md5 without salt - not used anymore
        $correct = md5($password) == $salt;
    }

    if ($correct && substr($salt, 0, strlen($crypt_alg)) != $crypt_alg && intval($uid)) {
        // this password is stored in another format than we want it to be.
        // let's update it!
        // we duplicate the query from the above set_password() function to have the extra safety of checking the old hash
        DB::update('
                UPDATE `User`
                SET `Passwort` = ?
                WHERE `UID` = ?
                AND `Passwort` = ?
                LIMIT 1
            ',
            [
                crypt($password, $crypt_alg . '$' . generate_salt() . '$'),
                $uid,
                $salt,
            ]
        );
    }
    return $correct;
}

/**
 * @param int $user_id
 * @return array
 */
function privileges_for_user($user_id)
{
    $privileges = [];
    $user_privileges = DB::select('
        SELECT `Privileges`.`name`
        FROM `User`
        JOIN `UserGroups` ON (`User`.`UID` = `UserGroups`.`uid`)
        JOIN `GroupPrivileges` ON (`UserGroups`.`group_id` = `GroupPrivileges`.`group_id`)
        JOIN `Privileges` ON (`GroupPrivileges`.`privilege_id` = `Privileges`.`id`)
        WHERE `User`.`UID`=?
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
    $groups_privileges = DB::select('
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
