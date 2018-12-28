<?php

use Engelsystem\Database\DB;
use Engelsystem\Models\User\User;

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
    $message = DB::selectOne('SELECT * FROM `Messages` WHERE `id`=? LIMIT 1', [$message_id]);

    return empty($message) ? null : $message;
}

/**
 * send message
 *
 * @param int    $receiver_user_id User ID of Receiver
 * @param string $text             Text of Message
 * @return bool
 */
function Message_send($receiver_user_id, $text)
{
    $user = auth()->user();
    $receiver = User::find($receiver_user_id);

    if (empty($text) || !$receiver || $receiver->id == $user->id) {
        return false;
    }

    return DB::insert('
            INSERT INTO `Messages` (`Datum`, `SUID`, `RUID`, `Text`)
            VALUES(?, ?, ?, ?)
            ',
        [
            time(),
            $user->id,
            $receiver->id,
            $text
        ]
    );
}
