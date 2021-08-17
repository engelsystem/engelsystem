<?php

use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;

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

/**
 * Render edit table.
 *
 * @param User  $user
 * @param Worklog $worklog
 * @return string
 */
function UserWorkLog_edit_form(User $user, Worklog $worklog)
{
    return form([
        form_info(__('User'), User_Nick_render($user)),
        form_date('work_timestamp', __('Work date'), $worklog->worked_at->timestamp, null, time()),
        form_text('work_hours', __('Work hours'), $worklog->hours),
        form_text('comment', __('Comment'), $worklog->comment, false, 200),
        form_submit('submit', __('Save'))
    ]);
}

/**
 * Form for edit a user work log entry.
 *
 * @param User  $user
 * @param Worklog $worklog
 * @return string
 */
function UserWorkLog_edit_view(User $user, Worklog $worklog)
{
    return page_with_title(UserWorkLog_edit_title(), [
        buttons([
            button(user_link($user->id), __('back'))
        ]),
        msg(),
        UserWorkLog_edit_form($user, $worklog)
    ]);
}

/**
 * Form for adding a user work log entry.
 *
 * @param User  $user
 * @param Worklog  $worklog
 * @return string
 */
function UserWorkLog_add_view(User $user, Worklog $worklog)
{
    return page_with_title(UserWorkLog_add_title(), [
        buttons([
            button(user_link($user->id), __('back'))
        ]),
        msg(),
        UserWorkLog_edit_form($user, $worklog)
    ]);
}

/**
 * Title text for editing work log entry.
 */
function UserWorkLog_edit_title()
{
    return __('Edit work log entry');
}

/**
 * Title text for adding work log entry.
 */
function UserWorkLog_add_title()
{
    return __('Add work log entry');
}
