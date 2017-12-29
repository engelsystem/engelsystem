<?php

/**
 * Delete a work log entry.
 */
function user_worklog_delete_controller()
{
    global $user;
    
    $request = request();
    $userWorkLog = UserWorkLog($request->input('user_worklog_id'));
    if (empty($userWorkLog)) {
        redirect(user_link($user));
    }
    $user_source = User($userWorkLog['user_id']);
    
    if ($request->has('confirmed')) {
        UserWorkLog_delete($userWorkLog);
        
        success(_('Work log entry deleted.'));
        redirect(user_link($user_source));
    }
    
    return [
        UserWorkLog_delete_title(),
        UserWorkLog_delete_view($user_source, $userWorkLog)
    ];
}

/**
 * Edit work log for user.
 */
function user_worklog_edit_controller()
{
    global $user;
    
    $request = request();
    $userWorkLog = UserWorkLog($request->input('user_worklog_id'));
    if (empty($userWorkLog)) {
        redirect(user_link($user));
    }
    $user_source = User($userWorkLog['user_id']);
    
    if ($request->has('submit')) {
        list ($valid, $userWorkLog) = user_worklog_from_request($userWorkLog);
        
        if ($valid) {
            UserWorkLog_update($userWorkLog);
            
            success(_('Work log entry updated.'));
            redirect(user_link($user_source));
        }
    }
    
    return [
        UserWorkLog_edit_title(),
        UserWorkLog_edit_view($user_source, $userWorkLog)
    ];
}

/**
 *
 * @param UserWorkLog $userWorkLog            
 * @return [bool $valid, UserWorkLog $userWorkLog]
 */
function user_worklog_from_request($userWorkLog)
{
    $request = request();
    
    $valid = true;
    
    $userWorkLog['work_timestamp'] = parse_date('Y-m-d H:i', $request->input('work_timestamp') . ' 00:00');
    if ($userWorkLog['work_timestamp'] == null) {
        $valid = false;
        error(_('Please enter work date.'));
    }
    
    $userWorkLog['work_hours'] = $request->input('work_hours');
    if (! preg_match("/[0-9]+(\.[0-9]+)?/", $userWorkLog['work_hours'])) {
        $valid = false;
        error(_('Please enter work hours in format ##[.##].'));
    }
    
    $userWorkLog['comment'] = $request->input('comment');
    if (empty($userWorkLog['comment'])) {
        $valid = false;
        error(_('Please enter a comment.'));
    }
    
    return [
        $valid,
        $userWorkLog
    ];
}

/**
 * Add work log entry to user.
 */
function user_worklog_add_controller()
{
    global $user;
    
    $request = request();
    $user_source = User($request->input('user_id'));
    if (empty($user_source)) {
        redirect(user_link($user));
    }
    
    $userWorkLog = UserWorkLog_new($user_source);
    
    if ($request->has('submit')) {
        list ($valid, $userWorkLog) = user_worklog_from_request($userWorkLog);
        
        if ($valid) {
            UserWorkLog_create($userWorkLog);
            
            success(_('Work log entry created.'));
            redirect(user_link($user_source));
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
 */
function user_worklog_add_link($user)
{
    return page_link_to('user_worklog', [
        'action' => 'add',
        'user_id' => $user['UID']
    ]);
}

/**
 * Link to work log entry edit.
 *
 * @param UserWorkLog $userWorkLog            
 */
function user_worklog_edit_link($userWorkLog)
{
    return page_link_to('user_worklog', [
        'action' => 'edit',
        'user_worklog_id' => $userWorkLog['id']
    ]);
}

/**
 * Link to work log entry delete.
 *
 * @param UserWorkLog $userWorkLog        
 * @param array[] $parameters    
 */
function user_worklog_delete_link($userWorkLog, $parameters = [])
{
    return page_link_to('user_worklog', array_merge([
        'action' => 'delete',
        'user_worklog_id' => $userWorkLog['id']
    ], $parameters));
}

/**
 * Work log entry actions
 */
function user_worklogs_controller()
{
    global $user, $privileges;
    
    if (! in_array('admin_user_worklog', $privileges)) {
        redirect(user_link($user));
    }
    
    $request = request();
    $action = $request->input('action');
    if (! $request->has('action')) {
        redirect(user_link($user));
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

?>