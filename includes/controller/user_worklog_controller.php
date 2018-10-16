<?php

use Engelsystem\Models\User\User;

/**
 * Delete a work log entry.
 *
 * @return array
 */
function user_worklog_delete_controller()
{
    $request = request();
    $userWorkLog = UserWorkLog($request->input('user_worklog_id'));
    if (empty($userWorkLog)) {
        redirect(user_link(auth()->user()->id));
    }
    $user_source = User::find($userWorkLog['user_id']);

    if ($request->has('confirmed')) {
        UserWorkLog_delete($userWorkLog);

        success(__('Work log entry deleted.'));
        redirect(user_link($user_source->id));
    }

    return [
        UserWorkLog_delete_title(),
        UserWorkLog_delete_view($user_source, $userWorkLog)
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
    $userWorkLog = UserWorkLog($request->input('user_worklog_id'));
    if (empty($userWorkLog)) {
        redirect(user_link(auth()->user()->id));
    }
    $user_source = User::find($userWorkLog['user_id']);

    if ($request->has('submit')) {
        list ($valid, $userWorkLog) = user_worklog_from_request($userWorkLog);

        if ($valid) {
            UserWorkLog_update($userWorkLog);

            success(__('Work log entry updated.'));
            redirect(user_link($user_source->id));
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
 * @param array $userWorkLog
 * @return array [bool $valid, UserWorkLog $userWorkLog]
 */
function user_worklog_from_request($userWorkLog)
{
    $request = request();

    $valid = true;

    $userWorkLog['work_timestamp'] = parse_date(
        'Y-m-d H:i',
        $request->input('work_timestamp') . ' 00:00'
    );
    if (is_null($userWorkLog['work_timestamp'])) {
        $valid = false;
        error(__('Please enter work date.'));
    }

    $userWorkLog['work_hours'] = $request->input('work_hours');
    if (!preg_match("/[0-9]+(\.[0-9]+)?/", $userWorkLog['work_hours'])) {
        $valid = false;
        error(__('Please enter work hours in format ##[.##].'));
    }

    $userWorkLog['comment'] = $request->input('comment');
    if (empty($userWorkLog['comment'])) {
        $valid = false;
        error(__('Please enter a comment.'));
    }

    return [
        $valid,
        $userWorkLog
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
        redirect(user_link(auth()->user()->id));
    }

    $userWorkLog = UserWorkLog_new($user_source->id);

    if ($request->has('submit')) {
        list ($valid, $userWorkLog) = user_worklog_from_request($userWorkLog);

        if ($valid) {
            UserWorkLog_create($userWorkLog);

            success(__('Work log entry created.'));
            redirect(user_link($user_source->id));
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
function user_worklog_add_link($user)
{
    return page_link_to('user_worklog', [
        'action'  => 'add',
        'user_id' => $user->id,
    ]);
}

/**
 * Link to work log entry edit.
 *
 * @param array $userWorkLog
 * @return string
 */
function user_worklog_edit_link($userWorkLog)
{
    return page_link_to('user_worklog', [
        'action'          => 'edit',
        'user_worklog_id' => $userWorkLog['id']
    ]);
}

/**
 * Link to work log entry delete.
 *
 * @param array   $userWorkLog
 * @param array[] $parameters
 * @return string
 */
function user_worklog_delete_link($userWorkLog, $parameters = [])
{
    return page_link_to('user_worklog', array_merge([
        'action'          => 'delete',
        'user_worklog_id' => $userWorkLog['id']
    ], $parameters));
}

/**
 * Work log entry actions
 *
 * @return array
 */
function user_worklog_controller()
{
    global $privileges;
    $user = auth()->user();

    if (!in_array('admin_user_worklog', $privileges)) {
        redirect(user_link($user->id));
    }

    $request = request();
    $action = $request->input('action');
    if (!$request->has('action')) {
        redirect(user_link($user->id));
    }

    switch ($action) {
        case 'add':
            return user_worklog_add_controller();
        case 'edit':
            return user_worklog_edit_controller();
        case 'delete':
            return user_worklog_delete_controller();
    }
}
