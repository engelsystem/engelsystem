<?php

use Engelsystem\Database\DB;

/**
 * Returns Message id array
 *
 * @return array
 */
function Message_ids()
{
    return DB::select('SELECT `id` FROM `Messages`');
}

/**
 * Returns message by id.
 *
 * @param int $message_id message ID
 * @return array|null
 */
function Message($message_id)
{
    $message_source = DB::select('SELECT * FROM `Messages` WHERE `id`=? LIMIT 1', [$message_id]);
    if (empty($message_source)) {
        return null;
    }
    return array_shift($message_source);
}

/**
 * TODO: use validation functions, return new message id
 * TODO: global $user con not be used in model!
 * send message
 *
 * @param int    $receiver_user_id User ID of Reciever
 * @param string $text             Text of Message
 * @return bool
 */
function Message_send($receiver_user_id, $text)
{
    global $user;

    $text = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}\n]{1,})/ui", '', strip_tags($text));
    $receiver_user_id = preg_replace('/([^\d]{1,})/ui', '', strip_tags($receiver_user_id));

    if (
        ($text != '' && is_numeric($receiver_user_id))
        && count(DB::select('
            SELECT `UID`
            FROM `User`
            WHERE `UID` = ?
            AND NOT `UID` = ?
            LIMIT 1
        ', [$receiver_user_id, $user['UID']])) > 0
    ) {
        return DB::insert('
            INSERT INTO `Messages` (`Datum`, `SUID`, `RUID`, `Text`)
            VALUES(?, ?, ?, ?)
            ',
            [
                time(),
                $user['UID'],
                $receiver_user_id,
                $text
            ]
        );
    }

    return false;
}
