<?php

use Engelsystem\Controllers\MessagesController;

/**
 * @return string
 */
function user_unread_messages()
{
    $count = app()->make(MessagesController::class)
        ->numberOfUnreadMessages();

    return $count > 0 ? ' <span class="badge bg-danger">' . $count . '</span>' : '';
}
