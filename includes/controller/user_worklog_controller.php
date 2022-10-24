<?php

use Engelsystem\Models\Worklog;

/**
 * Delete a work log entry.
 *
 * @return array
 */
function user_worklog_delete_controller()
{
    $request = request();
    $worklog = Worklog::find($request->input('user_worklog_id'));
    if (empty($worklog)) {
        throw_redirect(user_link(auth()->user()->id));
    }
    $user = $worklog->user;

    if ($request->hasPostData('submit')) {
        UserWorkLog_delete($worklog);

        success(__('Work log entry deleted.'));
        throw_redirect(user_link($user->id));
    }

    return [
        UserWorkLog_delete_title(),
        UserWorkLog_delete_view($user)
    ];
}

/**
 * Link to work log entry delete.
 *
 * @param Worklog $worklog
 * @param array[] $parameters
 * @return string
 */
function user_worklog_delete_link(Worklog $worklog, $parameters = [])
{
    return page_link_to('user_worklog', array_merge([
        'action'          => 'delete',
        'user_worklog_id' => $worklog->id
    ], $parameters));
}

/**
 * Work log entry actions
 *
 * @return array
 */
function user_worklog_controller()
{
    $user = auth()->user();

    if (!auth()->can('admin_user_worklog')) {
        throw_redirect(user_link($user->id));
    }

    $request = request();
    $action = $request->input('action');
    if (!$request->has('action')) {
        throw_redirect(user_link($user->id));
    }

    switch ($action) {
        case 'delete':
            return user_worklog_delete_controller();
    }

    return ['', ''];
}
