<?php

use Engelsystem\Models\User\User;

/**
 * Delete work log entry.
 *
 * @param User $user
 * @return string
 */
function UserWorkLog_delete_view(User $user)
{
    return page_with_title(UserWorkLog_delete_title(), [
        info(sprintf(
            __('Do you want to delete the worklog entry for %s?'),
            User_Nick_render($user)
        ), true),
        form([
            buttons([
                button(user_link($user->id), icon('x-lg') . __('cancel')),
                form_submit('submit', icon('check-lg') . __('delete'), 'btn-danger', false),
            ]),
        ]),
    ]);
}

/**
 * Title for work log delete.
 */
function UserWorkLog_delete_title()
{
    return __('Delete work log entry');
}
