<?php

use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;

/**
 * Delete a work log entry.
 *
 * @return array
 */
function user_worklog_delete_controller()
{
    $request = request();
    $userWorkLog = Worklog::find($request->input('user_worklog_id'));
    if (empty($userWorkLog)) {
        throw_redirect(user_link(auth()->user()->id));
    }
    $user_source = $userWorkLog->user;

    if ($request->hasPostData('submit')) {
        UserWorkLog_delete($userWorkLog);

        success(__('Work log entry deleted.'));
        throw_redirect(user_link($user_source->id));
    }

    return [
        UserWorkLog_delete_title(),
        UserWorkLog_delete_view($user_source)
    ];
}

/**
 * Edit work log for user.
 *
 * @return array
 */
function user_worklog_edit_controller()
{
    $request = request();
    $userWorkLog = Worklog::find($request->input('user_worklog_id'));
    if (empty($userWorkLog)) {
        throw_redirect(user_link(auth()->user()->id));
    }
    $user_source = $userWorkLog->user;

    if ($request->hasPostData('submit')) {
        list ($valid, $userWorkLog) = user_worklog_from_request($userWorkLog);

        if ($valid) {
            $userWorkLog->save();

            engelsystem_log(sprintf(
                'Updated work log for %s, %s hours, %s',
                User_Nick_render($userWorkLog->user, true),
                $userWorkLog->hours,
                $userWorkLog->comment
            ));

            success(__('Work log entry updated.'));
            throw_redirect(user_link($user_source->id));
        }
    }

    return [
        UserWorkLog_edit_title(),
        UserWorkLog_edit_view($user_source, $userWorkLog)
    ];
}

/**
 * Handle form
 *
 * @param Worklog $worklog
 * @return bool[]|Worklog[] [bool $valid, Worklog $userWorkLog]
 */
function user_worklog_from_request(Worklog $worklog)
{
    $request = request();

    $valid = true;

    $worklog->worked_at = DateTime::createFromFormat('Y-m-d H:i', $request->input('work_timestamp') . ' 00:00');
    if (!$worklog->worked_at) {
        $valid = false;
        error(__('Please enter work date.'));
    }

    $worklog->hours = $request->input('work_hours');
    if (!preg_match("/^[0-9]+(\.[0-9]{0,2})?$/", $worklog->hours)) {
        $valid = false;
        error(__('Please enter work hours in format ##[.##].'));
    }

    $worklog->comment = $request->input('comment');
    if (empty($worklog->comment)) {
        $valid = false;
        error(__('Please enter a comment.'));
    }

    if (mb_strlen($worklog->comment) > 200) {
        $valid = false;
        error(__('Comment too long.'));
    }

    return [
        $valid,
        $worklog
    ];
}

/**
 * Add work log entry to user.
 *
 * @return array
 */
function user_worklog_add_controller()
{
    $request = request();
    $user_source = User::find($request->input('user_id'));
    if (!$user_source) {
        throw_redirect(user_link(auth()->user()->id));
    }

    $userWorkLog = UserWorkLog_new($user_source->id);

    if ($request->hasPostData('submit')) {
        list ($valid, $userWorkLog) = user_worklog_from_request($userWorkLog);

        if ($valid) {
            UserWorkLog_create($userWorkLog);

            success(__('Work log entry created.'));
            throw_redirect(user_link($user_source->id));
        }
    }

    return [
        UserWorkLog_add_title(),
        UserWorkLog_add_view($user_source, $userWorkLog)
    ];
}

/**
 * Link to work log entry add for given user.
 *
 * @param User $user
 *
 * @return string
 */
function user_worklog_add_link(User $user)
{
    return page_link_to('user_worklog', [
        'action'  => 'add',
        'user_id' => $user->id,
    ]);
}

/**
 * Link to work log entry edit.
 *
 * @param Worklog $userWorkLog
 * @return string
 */
function user_worklog_edit_link(Worklog $userWorkLog)
{
    return page_link_to('user_worklog', [
        'action'          => 'edit',
        'user_worklog_id' => $userWorkLog->id
    ]);
}

/**
 * Link to work log entry delete.
 *
 * @param Worklog $userWorkLog
 * @param array[] $parameters
 * @return string
 */
function user_worklog_delete_link(Worklog $userWorkLog, $parameters = [])
{
    return page_link_to('user_worklog', array_merge([
        'action'          => 'delete',
        'user_worklog_id' => $userWorkLog->id
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
        case 'add':
            return user_worklog_add_controller();
        case 'edit':
            return user_worklog_edit_controller();
        case 'delete':
            return user_worklog_delete_controller();
    }

    return ['', ''];
}
