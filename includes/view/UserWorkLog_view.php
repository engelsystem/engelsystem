<?php

use Engelsystem\Models\User\User;

/**
 * Delete work log entry.
 *
 * @param User $user_source
 * @return string
 */
function UserWorkLog_delete_view($user_source)
{
    return page_with_title(UserWorkLog_delete_title(), [
        info(sprintf(
            __('Do you want to delete the worklog entry for %s?'),
            User_Nick_render($user_source)
        ), true),
        form([
            buttons([
                button(user_link($user_source->id), glyph('remove') . __('cancel')),
                form_submit('submit', glyph('ok') . __('delete'), 'btn-danger', false),
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
 * @param User  $user_source
 * @param array $userWorkLog
 * @return string
 */
function UserWorkLog_edit_form($user_source, $userWorkLog)
{
    return form([
        form_info(__('User'), User_Nick_render($user_source)),
        form_date('work_timestamp', __('Work date'), $userWorkLog['work_timestamp'], null, time()),
        form_text('work_hours', __('Work hours'), $userWorkLog['work_hours']),
        form_text('comment', __('Comment'), $userWorkLog['comment']),
        form_submit('submit', __('Save'))
    ]);
}

/**
 * Form for edit a user work log entry.
 *
 * @param User  $user_source
 * @param array $userWorkLog
 * @return string
 */
function UserWorkLog_edit_view($user_source, $userWorkLog)
{
    return page_with_title(UserWorkLog_edit_title(), [
        buttons([
            button(user_link($user_source->id), __('back'))
        ]),
        msg(),
        UserWorkLog_edit_form($user_source, $userWorkLog)
    ]);
}

/**
 * Form for adding a user work log entry.
 *
 * @param User  $user_source
 * @param array $userWorkLog
 * @return string
 */
function UserWorkLog_add_view($user_source, $userWorkLog)
{
    return page_with_title(UserWorkLog_add_title(), [
        buttons([
            button(user_link($user_source->id), __('back'))
        ]),
        msg(),
        UserWorkLog_edit_form($user_source, $userWorkLog)
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
