<?php

use Engelsystem\Controllers\MessagesController;

/**
 * @return string
 */
function user_unread_messages()
{
    $count = app()->make(MessagesController::class)
        ->number_of_unread_messages();

    return $count > 0 ? ' <span class="badge bg-danger">' . $count . '</span>' : '';
}
