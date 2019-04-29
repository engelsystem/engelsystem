<?php

use Engelsystem\Models\LogEntry;

/**
 * @return string
 */
function admin_log_title()
{
    return __('Log');
}

/**
 * @return string
 */
function admin_log()
{
    $filter = '';
    if (request()->has('keyword')) {
        $filter = strip_request_item('keyword');
    }

    $log_entries = LogEntry::filter($filter);

    $entries = [];
    foreach ($log_entries as $entry) {
        $data = $entry->toArray();
        $data['message'] = nl2br(htmlspecialchars($data['message']));
        $data['created_at'] = date_format($entry->created_at, 'd.m.Y H:i');
        $entries[] = $data;
    }

    return page_with_title(admin_log_title(), [
        msg(),
        form([
            form_text('keyword', __('Search'), $filter),
            form_submit(__('Search'), 'Go')
        ]),
        table([
            'created_at' => 'Time',
            'level'      => 'Type',
            'message'    => 'Log Entry'
        ], $entries)
    ]);
}
